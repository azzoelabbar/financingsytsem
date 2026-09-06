<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Enums\Accounting\ClosingBehavior;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Investment\Investment;
use App\Models\Project\Project;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ap\ApLedgerService;
use App\Services\Ar\ArLedgerService;
use App\Services\Budget\BudgetService;
use App\Services\Project\ProjectReportingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportingPackService
{
    public function __construct(
        private readonly TrialBalanceService $trialBalance,
        private readonly FinancialStatementService $statements,
        private readonly CashFlowService $cashFlow,
        private readonly ArLedgerService $ar,
        private readonly ApLedgerService $ap,
        private readonly BudgetService $budget,
        private readonly ProjectReportingService $projects,
        private readonly AccountRoleResolver $roles,
    ) {}

    /**
     * @return array{lines: list<array{code: string, name: string, amount: numeric-string, journal_ids: list<int>}>, total: numeric-string}
     */
    public function otherComprehensiveIncome(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        $tb = $this->trialBalance->build($company, $book, $asOf)->keyBy('code');
        $ociAccounts = Account::query()
            ->where('company_id', $company->id)
            ->where(function ($q): void {
                $q->where('closing_behavior', ClosingBehavior::OCI->value)->orWhere('is_oci', true);
            })
            ->where('is_posting', true)
            ->orderBy('code')
            ->get();

        $lines = [];
        $total = Decimal::of('0');
        foreach ($ociAccounts as $account) {
            $row = $tb->get($account->code);
            $amount = $row === null ? Decimal::of('0') : Decimal::sub($row->credit, $row->debit);
            if (Decimal::equals($amount, '0')) {
                continue;
            }
            $lines[] = [
                'code' => $account->code,
                'name' => (string) $account->name_ar,
                'amount' => $amount,
                'journal_ids' => $this->journalIdsForAccount($company, $book, $account->code),
            ];
            $total = Decimal::add($total, $amount);
        }

        return ['lines' => $lines, 'total' => $total];
    }

    /**
     * @return array{inflows: numeric-string, outflows: numeric-string, net: numeric-string, ar: array<string, mixed>, ap: array<string, mixed>}
     */
    public function cashForecast(Company $company, ?Carbon $asOf = null, ?AccountingBook $book = null): array
    {
        $asOf ??= Carbon::now();
        $ar = $this->ar->aging($company, $asOf, book: $book);
        $ap = $this->ap->aging($company, $asOf, book: $book);
        $in = Decimal::add($ar['buckets']['current'], $ar['buckets']['1_30']);
        $out = Decimal::add($ap['buckets']['current'], $ap['buckets']['1_30']);

        return [
            'inflows' => $in,
            'outflows' => $out,
            'net' => Decimal::sub($in, $out),
            'ar' => $ar,
            'ap' => $ap,
        ];
    }

    /**
     * @return list<array{code: string, ifrs: ?string, amount: numeric-string, source: string}>
     */
    public function ifrsNotes(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        $tb = $this->trialBalance->build($company, $book, $asOf)->keyBy('code');
        $notes = [];
        $accounts = Account::query()
            ->where('company_id', $company->id)
            ->where('is_posting', true)
            ->whereNotNull('ifrs_reference')
            ->where('ifrs_reference', '!=', '')
            ->orderBy('code')
            ->get();

        foreach ($accounts as $account) {
            $row = $tb->get($account->code);
            if ($row === null || Decimal::equals($row->balance, '0')) {
                continue;
            }
            $notes[] = [
                'code' => $account->code,
                'ifrs' => $account->ifrs_reference,
                'amount' => $row->balance,
                'source' => 'gl:'.$account->code,
            ];
        }

        return $notes;
    }

    /**
     * @return list<array{code: string, classification: string, carrying: numeric-string, fair_value: ?numeric-string, journal_id: ?int}>
     */
    public function investmentSummary(Company $company): array
    {
        $rows = [];
        foreach (Investment::query()->where('company_id', $company->id)->orderBy('code')->get() as $inv) {
            $rows[] = [
                'code' => $inv->code,
                'classification' => $inv->classification->value,
                'carrying' => Decimal::of($inv->carrying_amount),
                'fair_value' => $inv->fair_value !== null ? Decimal::of($inv->fair_value) : null,
                'journal_id' => $inv->journal_id,
            ];
        }

        return $rows;
    }

    /**
     * @return array{output: numeric-string, input: numeric-string, net: numeric-string}
     */
    public function taxSummary(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        $tb = $this->trialBalance->build($company, $book, $asOf)->keyBy('code');
        $outputCode = $this->roles->code($company, 'tax.output_vat');
        $inputCode = $this->roles->code($company, 'tax.input_vat');
        $outRow = $tb->get($outputCode);
        $inRow = $tb->get($inputCode);
        $output = $outRow === null ? Decimal::of('0') : Decimal::sub($outRow->credit, $outRow->debit);
        $input = $inRow === null ? Decimal::of('0') : Decimal::sub($inRow->debit, $inRow->credit);

        return [
            'output' => $output,
            'input' => $input,
            'net' => Decimal::sub($output, $input),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function managementPack(Company $company, AccountingBook $book, ReportRequest $request): array
    {
        $projectPack = [];
        foreach (Project::query()->where('company_id', $company->id)->get() as $project) {
            $projectPack[] = $this->projects->summary($project);
        }
        $periodKey = Carbon::now()->format('Y-m');
        $budgetLine = $this->budget->variance(
            $company,
            $book,
            $periodKey,
            $this->roles->code($company, 'expense.default'),
            Carbon::parse(Carbon::now()->toDateString()),
        );

        return [
            'balance_sheet' => $this->statements->balanceSheet($request),
            'income_statement' => $this->statements->incomeStatement($request),
            'cash_flow' => $this->cashFlow->summary($company, $book),
            'oci' => $this->otherComprehensiveIncome($company, $book),
            'ar_aging' => $this->ar->aging($company, book: $book),
            'ap_aging' => $this->ap->aging($company, book: $book),
            'cash_forecast' => $this->cashForecast($company, book: $book),
            'budget_vs_actual' => $budgetLine,
            'project_costs' => $projectPack,
            'investment_summary' => $this->investmentSummary($company),
            'tax_summary' => $this->taxSummary($company, $book),
            'notes' => $this->ifrsNotes($company, $book),
            'book_id' => $book->id,
            'book_basis' => $book->basis->value,
        ];
    }

    /** @return list<int> */
    private function journalIdsForAccount(Company $company, AccountingBook $book, string $code): array
    {
        $raw = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.company_id', $company->id)
            ->where('j.book_id', $book->id)
            ->where('a.code', $code)
            ->distinct()
            ->orderBy('j.id')
            ->pluck('j.id');

        $ids = [];
        foreach ($raw as $id) {
            $ids[] = (int) $id;
        }

        return $ids;
    }
}
