<?php

declare(strict_types=1);

namespace App\Services\Gl;

use App\Enums\Accounting\ClosingBehavior;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Journal;
use App\Models\User;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Gl\Exceptions\GlException;
use Illuminate\Support\Carbon;

/**
 * Year-end P&L close → Retained Earnings (closing_behavior=retained_earnings / 320201).
 * Posts a system journal that zeros temporary income-statement accounts into RE.
 */
class YearEndCloseService
{
    public function __construct(
        private readonly JournalService $journals,
        private readonly TrialBalanceService $trialBalance,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    public function closeToRetainedEarnings(
        Company $company,
        AccountingBook $book,
        FiscalPeriod $period,
        ?User $poster = null,
        ?string $retainedEarningsCode = null,
    ): Journal {
        $reCode = $retainedEarningsCode ?? $this->retainedEarningsCode($company);
        $reAccount = $this->controls->assertPostingAccount($company, $reCode);

        $asOf = Carbon::parse($period->end_date);
        $rows = $this->trialBalance->build($company, $book, $asOf);
        $accountMeta = Account::query()
            ->where('company_id', $company->id)
            ->whereIn('id', $rows->pluck('accountId')->all())
            ->get()
            ->keyBy('id');

        $lines = [];
        $plug = '0';

        foreach ($rows as $row) {
            /** @var Account|null $account */
            $account = $accountMeta[$row->accountId] ?? null;
            if ($account === null || ! $account->account_type->isTemporary()) {
                continue;
            }
            if (Decimal::equals($row->balance, '0')) {
                continue;
            }

            // balance = debit − credit. Reverse the net to zero the account.
            if (Decimal::isPositive($row->balance)) {
                $lines[] = LineInput::credit($account->id, $row->balance, 'Year-end close');
                $plug = Decimal::add($plug, $row->balance);
            } else {
                $creditBal = Decimal::sub('0', $row->balance);
                $lines[] = LineInput::debit($account->id, $creditBal, 'Year-end close');
                $plug = Decimal::sub($plug, $creditBal);
            }
        }

        if ($lines === []) {
            throw new GlException('No P&L balances to close for the period.');
        }

        if (Decimal::isPositive($plug)) {
            $lines[] = LineInput::debit($reAccount->id, $plug, 'Retained earnings');
        } elseif (Decimal::isNegative($plug)) {
            $lines[] = LineInput::credit($reAccount->id, Decimal::sub('0', $plug), 'Retained earnings');
        } else {
            throw new PostingException('Year-end close produced a zero plug unexpectedly with P&L lines.');
        }

        $journal = $this->journals->createAndPost($company, $book, [
            'journal_date' => $asOf->toDateString(),
            'source' => 'year_end_close',
            'reference' => 'YE-'.$period->id,
            'description' => 'Year-end close to retained earnings',
            'is_system_generated' => true,
            'created_by' => $poster?->id,
        ], $lines, $poster);

        $this->audit->record($journal, 'year_end_close', $company->id, null, [
            'fiscal_period_id' => $period->id,
            'retained_earnings' => $reCode,
        ]);

        return $journal;
    }

    private function retainedEarningsCode(Company $company): string
    {
        $account = Account::query()
            ->where('company_id', $company->id)
            ->where('is_posting', true)
            ->where(function ($q): void {
                $q->where('closing_behavior', ClosingBehavior::RETAINED_EARNINGS->value)
                    ->orWhere('code', '320201');
            })
            ->orderBy('code')
            ->first();

        if ($account === null) {
            throw new GlException("Company {$company->code} has no retained earnings account on the chart.");
        }

        return $account->code;
    }
}
