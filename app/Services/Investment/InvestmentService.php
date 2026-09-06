<?php

declare(strict_types=1);

namespace App\Services\Investment;

use App\Enums\Investment\Classification;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\Investment\Investment;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;
use ValueError;

class InvestmentService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly AccountRoleResolver $roles,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function acquire(Company $company, AccountingBook $book, array $data): Investment
    {
        try {
            $class = Classification::parse(is_string($data['classification'] ?? null) ? $data['classification'] : '');
        } catch (ValueError) {
            throw new PostingException('Invalid investment classification.');
        }

        $qty = Decimal::of(is_scalar($data['quantity'] ?? null) ? (string) $data['quantity'] : '1');
        $unit = isset($data['unit_cost']) && is_scalar($data['unit_cost'])
            ? Decimal::of((string) $data['unit_cost'])
            : Decimal::of('0');
        $cost = isset($data['cost']) && is_scalar($data['cost'])
            ? Decimal::of((string) $data['cost'])
            : Decimal::mul($qty, $unit);
        if (! Decimal::isPositive($qty)) {
            throw new PostingException('Investment quantity must be positive.');
        }
        if (! Decimal::isPositive($cost)) {
            throw new PostingException('Investment acquisition amount must be positive.');
        }
        if (! Decimal::isPositive($unit)) {
            $unit = Decimal::div($cost, $qty);
        }

        $date = is_string($data['date'] ?? $data['acquired_at'] ?? null)
            ? (string) ($data['date'] ?? $data['acquired_at'])
            : now()->toDateString();
        $currency = is_string($data['currency'] ?? null) ? $data['currency'] : (string) $company->functional_currency;
        $rate = is_numeric($data['exchange_rate'] ?? null) ? (float) $data['exchange_rate'] : 1.0;
        $gl = $this->roles->code($company, $class->glRole());
        $bank = $this->roles->code($company, 'bank');
        $dims = $this->dimensions($data);
        $number = is_string($data['code'] ?? $data['investment_number'] ?? null)
            ? (string) ($data['code'] ?? $data['investment_number'])
            : throw new PostingException('Investment number is required.');

        return DB::transaction(function () use ($company, $book, $data, $class, $qty, $unit, $cost, $date, $currency, $rate, $gl, $bank, $dims, $number): Investment {
            $journal = $this->post($company, 'investment.acquire', $date, $currency, $number, [
                ['account' => $gl, 'debit' => $cost, 'memo' => 'Investment acquisition', 'dimensions' => $dims],
                ['account' => $bank, 'credit' => $cost, 'memo' => 'Settlement', 'dimensions' => $dims],
            ], $rate);

            $investment = Investment::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'code' => $number,
                'investment_number' => $number,
                'name' => $data['name'] ?? $number,
                'instrument_type' => $data['instrument_type'] ?? 'equity',
                'classification' => $class,
                'currency' => $currency,
                'quantity' => $qty,
                'unit_cost' => $unit,
                'acquisition_cost' => $cost,
                'carrying_amount' => $cost,
                'carrying' => $cost,
                'cost' => $cost,
                'fair_value' => $cost,
                'effective_interest_rate' => isset($data['effective_interest_rate']) ? Decimal::of((string) $data['effective_interest_rate']) : null,
                'gl_account_code' => $gl,
                'status' => 'active',
                'acquired_at' => $date,
                'maturity_at' => $data['maturity_at'] ?? null,
                'dimensions' => $dims === [] ? null : $dims,
                'journal_id' => $journal->id,
            ]);
            $this->recordTx($investment, 'acquire', $date, $cost, $currency, $rate, $journal);
            $this->audit->record($investment, 'created', $company->id, null, ['number' => $number, 'journal_id' => $journal->id]);

            return $investment;
        });
    }

    public function revalue(Investment $investment, string $date, string|float|int $fairValue): Investment
    {
        $this->assertActive($investment);
        $class = $investment->classification;
        if ($class === Classification::AMORTIZED_COST) {
            throw new PostingException('Amortized-cost instruments are not revalued to fair value through P&L/OCI.');
        }
        $fv = Decimal::of($fairValue);
        $diff = Decimal::sub($fv, Decimal::of($investment->carrying_amount));
        if (Decimal::equals($diff, '0')) {
            throw new PostingException('No fair-value movement to post.');
        }
        $abs = Decimal::isNegative($diff) ? Decimal::sub('0', $diff) : $diff;
        $company = $investment->company;
        $gl = $investment->gl_account_code;
        $oci = $class === Classification::FVOCI;
        $gainRole = $oci ? 'investment.oci_reserve' : 'investment.fv_gain';
        $lossRole = $oci ? 'investment.oci_reserve' : 'investment.fv_loss';
        $gain = $this->roles->code($company, $gainRole);
        $loss = $this->roles->code($company, $lossRole);
        $dims = is_array($investment->dimensions) ? $investment->dimensions : [];
        $movements = Decimal::isPositive($diff)
            ? [
                ['account' => $gl, 'debit' => $abs, 'memo' => 'FV increase', 'dimensions' => $dims],
                ['account' => $gain, 'credit' => $abs, 'memo' => $oci ? 'FVOCI reserve' : 'FVTPL gain', 'dimensions' => $dims],
            ]
            : [
                ['account' => $loss, 'debit' => $abs, 'memo' => $oci ? 'FVOCI reserve' : 'FVTPL loss', 'dimensions' => $dims],
                ['account' => $gl, 'credit' => $abs, 'memo' => 'FV decrease', 'dimensions' => $dims],
            ];

        $journal = $this->post($company, 'investment.revalue', $date, (string) $investment->currency, $investment->code, $movements);
        $investment->forceFill([
            'carrying_amount' => $fv,
            'carrying' => $fv,
            'fair_value' => $fv,
        ])->save();
        $investment->valuations()->create([
            'valued_at' => $date,
            'fair_value' => $fv,
            'movement' => $diff,
            'journal_id' => $journal->id,
        ]);
        $this->recordTx($investment, 'revalue', $date, $abs, (string) $investment->currency, 1.0, $journal);
        $this->audit->record($investment, 'revalued', $company->id, null, ['fair_value' => $fv, 'journal_id' => $journal->id]);

        return $investment->fresh() ?? $investment;
    }

    public function dispose(Investment $investment, string $date, string|float|int $proceeds): Investment
    {
        $this->assertActive($investment);
        $cash = Decimal::of($proceeds);
        if (! Decimal::isPositive($cash)) {
            throw new PostingException('Disposal proceeds must be positive.');
        }
        $company = $investment->company;
        $carrying = Decimal::of($investment->carrying_amount);
        $diff = Decimal::sub($cash, $carrying);
        $dims = is_array($investment->dimensions) ? $investment->dimensions : [];
        $bank = $this->roles->code($company, 'bank');
        $movements = [
            ['account' => $bank, 'debit' => $cash, 'memo' => 'Sale proceeds', 'dimensions' => $dims],
            ['account' => $investment->gl_account_code, 'credit' => $carrying, 'memo' => 'Derecognise carrying', 'dimensions' => $dims],
        ];
        if ($investment->classification === Classification::FVOCI) {
            $oci = $this->roles->code($company, 'investment.oci_reserve');
            $ociAmount = Decimal::sub(Decimal::of($investment->carrying_amount), Decimal::of($investment->acquisition_cost));
            if (Decimal::isPositive($ociAmount)) {
                $movements[] = ['account' => $oci, 'debit' => $ociAmount, 'memo' => 'Recycle OCI', 'dimensions' => $dims];
                $movements[] = ['account' => $this->roles->code($company, 'investment.disposal_gain'), 'credit' => $ociAmount, 'memo' => 'Recycle OCI to P&L', 'dimensions' => $dims];
            } elseif (Decimal::isNegative($ociAmount)) {
                $abs = Decimal::sub('0', $ociAmount);
                $movements[] = ['account' => $this->roles->code($company, 'investment.disposal_loss'), 'debit' => $abs, 'memo' => 'Recycle OCI to P&L', 'dimensions' => $dims];
                $movements[] = ['account' => $oci, 'credit' => $abs, 'memo' => 'Recycle OCI', 'dimensions' => $dims];
            }
        }
        if (Decimal::isPositive($diff)) {
            $movements[] = ['account' => $this->roles->code($company, 'investment.disposal_gain'), 'credit' => $diff, 'memo' => 'Disposal gain', 'dimensions' => $dims];
        } elseif (Decimal::isNegative($diff)) {
            $movements[] = ['account' => $this->roles->code($company, 'investment.disposal_loss'), 'debit' => Decimal::sub('0', $diff), 'memo' => 'Disposal loss', 'dimensions' => $dims];
        }

        $journal = $this->post($company, 'investment.dispose', $date, (string) $investment->currency, $investment->code, $movements);
        $investment->forceFill([
            'status' => 'disposed',
            'carrying_amount' => '0',
            'carrying' => '0',
        ])->save();
        $investment->disposals()->create([
            'disposed_at' => $date,
            'proceeds' => $cash,
            'carrying_amount' => $carrying,
            'gain_loss' => $diff,
            'journal_id' => $journal->id,
        ]);
        $this->recordTx($investment, 'dispose', $date, $cash, (string) $investment->currency, 1.0, $journal);
        $this->audit->record($investment, 'disposed', $company->id, null, ['journal_id' => $journal->id]);

        return $investment->fresh() ?? $investment;
    }

    public function dividend(Investment $investment, string $date, string|float|int $amount): Investment
    {
        return app(InvestmentIncomeService::class)->dividend($investment, $date, $amount);
    }

    /**
     * @param  list<array{account: string, debit?: string, credit?: string, memo?: string, dimensions?: array<string, string>}>  $movements
     */
    private function post(Company $company, string $type, string $date, string $currency, string $ref, array $movements, float $rate = 1.0): Journal
    {
        return $this->poster->post($company, $type, $date, $currency, $ref, $movements, $rate);
    }

    private function assertActive(Investment $investment): void
    {
        if ($investment->status !== 'active') {
            throw new PostingException('Investment is not active.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function dimensions(array $data): array
    {
        $raw = $data['dimensions'] ?? [];
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $k => $v) {
            if (is_string($k) && is_string($v)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    private function recordTx(Investment $investment, string $type, string $date, string $amount, string $currency, float $rate, Journal $journal): void
    {
        $investment->transactions()->create([
            'company_id' => $investment->company_id,
            'type' => $type,
            'transacted_at' => $date,
            'amount' => $amount,
            'currency' => $currency,
            'exchange_rate' => $rate,
            'journal_id' => $journal->id,
        ]);
    }
}
