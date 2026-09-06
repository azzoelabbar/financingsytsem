<?php

declare(strict_types=1);

namespace App\Services\Tax;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Support\Carbon;

class TaxService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly TrialBalanceService $trialBalance,
    ) {}

    public function settleVat(Company $company, AccountingBook $book, Carbon|string $asOf, string $outputCode = '210404', string $inputCode = '110210', string $payableCode = '210405'): Journal
    {
        $asOfDate = Carbon::parse($asOf);
        $rows = $this->trialBalance->build($company, $book, $asOfDate)->keyBy('code');
        $output = $this->creditBalance($rows->get($outputCode));
        $input = $this->debitBalance($rows->get($inputCode));
        if (! Decimal::isPositive($output) && ! Decimal::isPositive($input)) {
            throw new PostingException('No VAT balances to settle.');
        }
        $net = Decimal::sub($output, $input);

        $movements = [];
        if (Decimal::isPositive($output)) {
            $movements[] = ['account' => $outputCode, 'debit' => $output, 'memo' => 'Clear output VAT'];
        }
        if (Decimal::isPositive($input)) {
            $movements[] = ['account' => $inputCode, 'credit' => $input, 'memo' => 'Clear input VAT'];
        }
        if (Decimal::isPositive($net)) {
            $movements[] = ['account' => $payableCode, 'credit' => $net, 'memo' => 'VAT payable'];
        } elseif (Decimal::isNegative($net)) {
            $movements[] = ['account' => $payableCode, 'debit' => Decimal::sub('0', $net), 'memo' => 'VAT receivable'];
        }

        return $this->poster->post($company, 'tax.settlement', $asOfDate->toDateString(), (string) $company->functional_currency, 'VAT-'.$asOfDate->format('Ym'), $movements);
    }

    public function withhold(Company $company, string $date, string $expenseOrAp, string $whtAccount, string $amount): Journal
    {
        return $this->poster->post($company, 'tax.withholding', $date, (string) $company->functional_currency, 'WHT', [
            ['account' => $expenseOrAp, 'debit' => $amount, 'memo' => 'Withholding'],
            ['account' => $whtAccount, 'credit' => $amount, 'memo' => 'WHT payable'],
        ]);
    }

    public function adjust(Company $company, string $date, string $from, string $to, string $amount, string $memo = 'Tax adjustment'): Journal
    {
        return $this->poster->post($company, 'tax.adjustment', $date, (string) $company->functional_currency, 'TAX-ADJ', [
            ['account' => $from, 'debit' => $amount, 'memo' => $memo],
            ['account' => $to, 'credit' => $amount, 'memo' => $memo],
        ]);
    }

    public function postDeferred(Company $company, string $date, string $amount, bool $liability = true): Journal
    {
        if ($liability) {
            return $this->poster->post($company, 'tax.deferred', $date, (string) $company->functional_currency, 'DTL', [
                ['account' => '640102', 'debit' => $amount, 'memo' => 'Deferred tax expense'],
                ['account' => '220501', 'credit' => $amount, 'memo' => 'DTL'],
            ]);
        }

        return $this->poster->post($company, 'tax.deferred', $date, (string) $company->functional_currency, 'DTA', [
            ['account' => '120401', 'debit' => $amount, 'memo' => 'DTA'],
            ['account' => '640102', 'credit' => $amount, 'memo' => 'Deferred tax benefit'],
        ]);
    }

    /** @return numeric-string */
    private function creditBalance(mixed $row): string
    {
        if ($row === null) {
            return Decimal::of('0');
        }

        return Decimal::sub(Decimal::of($row->credit), Decimal::of($row->debit));
    }

    /** @return numeric-string */
    private function debitBalance(mixed $row): string
    {
        if ($row === null) {
            return Decimal::of('0');
        }

        return Decimal::sub(Decimal::of($row->debit), Decimal::of($row->credit));
    }
}
