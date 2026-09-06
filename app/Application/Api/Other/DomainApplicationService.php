<?php

declare(strict_types=1);

namespace App\Application\Api\Other;

use App\Application\Api\ApiQuery;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Assets\FixedAsset;
use App\Models\Budget\BudgetLine;
use App\Models\Expense\ExpenseClaim;
use App\Models\Gl\OpeningBalanceBatch;
use App\Models\Investment\Investment;
use App\Models\Project\Project;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxRate;
use App\Services\Accounting\MultiBookService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\ReportingPackService;
use App\Services\Assets\AssetService;
use App\Services\Budget\BudgetService;
use App\Services\Expense\ExpenseClaimService;
use App\Services\Expense\ExpenseReimbursementService;
use App\Services\Gl\OpeningBalanceService;
use App\Services\Investment\InvestmentService;
use App\Services\Project\ProjectService;
use App\Services\Tax\TaxEngine;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class DomainApplicationService
{
    public function __construct(
        private readonly InvestmentService $investments,
        private readonly ExpenseClaimService $expenses,
        private readonly ExpenseReimbursementService $reimbursements,
        private readonly ProjectService $projects,
        private readonly TaxEngine $tax,
        private readonly OpeningBalanceService $openingBalances,
        private readonly MultiBookService $multiBook,
        private readonly ReportingPackService $reporting,
        private readonly BudgetService $budgets,
        private readonly AssetService $assets,
    ) {}

    /** @return LengthAwarePaginator<int, Investment> */
    public function listInvestments(Company $company, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            Investment::query()->where('company_id', $company->id),
            $request,
            ['id', 'code', 'classification', 'status', 'created_at'],
            ['code' => 'code', 'classification' => 'classification', 'status' => 'status'],
            'code',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function acquireInvestment(Company $company, AccountingBook $book, array $data): Investment
    {
        return $this->investments->acquire($company, $book, $data);
    }

    public function revalueInvestment(Investment $investment, string $date, string|float|int $fairValue): Investment
    {
        return $this->investments->revalue($investment, $date, $fairValue);
    }

    public function recordInvestmentIncome(Investment $investment, string $date, string|float|int $amount): Investment
    {
        return $this->investments->dividend($investment, $date, $amount);
    }

    public function disposeInvestment(Investment $investment, string $date, string|float|int $proceeds): Investment
    {
        return $this->investments->dispose($investment, $date, $proceeds);
    }

    /** @param array<string, mixed> $data */
    public function acquireAsset(Company $company, AccountingBook $book, array $data): FixedAsset
    {
        return $this->assets->acquire($company, $book, $data);
    }

    public function depreciateAsset(FixedAsset $asset, string $date): FixedAsset
    {
        return $this->assets->depreciate($asset, $date);
    }

    public function disposeAsset(FixedAsset $asset, string $date, string|float|int $proceeds): FixedAsset
    {
        return $this->assets->dispose($asset, $date, $proceeds);
    }

    public function findInvestment(Company $company, int $id): Investment
    {
        return Investment::query()->where('company_id', $company->id)->where('id', $id)->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, ExpenseClaim> */
    public function listExpenses(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            ExpenseClaim::query()->where('company_id', $company->id)->where('book_id', $book->id),
            $request,
            ['id', 'number', 'claim_date', 'status', 'created_at'],
            ['status' => 'status', 'number' => 'number'],
            'claim_date',
        );
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function createExpenseDraft(Company $company, AccountingBook $book, array $header, array $lines): ExpenseClaim
    {
        return $this->expenses->createDraft($company, $book, $header, $lines);
    }

    public function findExpense(Company $company, AccountingBook $book, int $id): ExpenseClaim
    {
        return ExpenseClaim::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function submitExpense(ExpenseClaim $claim): ExpenseClaim
    {
        return $this->expenses->submit($claim);
    }

    public function approveExpense(ExpenseClaim $claim): ExpenseClaim
    {
        return $this->expenses->approve($claim);
    }

    public function rejectExpense(ExpenseClaim $claim, ?string $reason = null): ExpenseClaim
    {
        return $this->expenses->reject($claim, null, $reason);
    }

    public function postExpense(ExpenseClaim $claim): ExpenseClaim
    {
        return $this->expenses->post($claim);
    }

    public function reimburseExpense(ExpenseClaim $claim, string $date): mixed
    {
        return $this->reimbursements->reimburse($claim, $date);
    }

    /** @return LengthAwarePaginator<int, Project> */
    public function listProjects(Company $company, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            Project::query()->where('company_id', $company->id),
            $request,
            ['id', 'code', 'name', 'status', 'created_at'],
            ['code' => 'code', 'status' => 'status'],
            'code',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function defineProject(Company $company, array $data): Project
    {
        return $this->projects->define($company, $data);
    }

    public function findProject(Company $company, int $id): Project
    {
        return Project::query()->where('company_id', $company->id)->where('id', $id)->firstOrFail();
    }

    public function chargeProject(Project $project, string $date, string|float|int $amount): Project
    {
        return $this->projects->charge($project, $date, $amount);
    }

    public function capitalizeProject(Project $project, string $date, string|float|int $amount): Project
    {
        return $this->projects->capitalize($project, $date, $amount);
    }

    /** @return LengthAwarePaginator<int, TaxCode> */
    public function listTaxCodes(Company $company, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            TaxCode::query()->where('company_id', $company->id),
            $request,
            ['id', 'code', 'name', 'kind', 'created_at'],
            ['code' => 'code', 'kind' => 'kind'],
            'code',
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function defineTaxCode(Company $company, array $attributes): TaxCode
    {
        return $this->tax->defineCode($company, $attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function approveTaxRate(TaxCode $code, array $attributes): TaxRate
    {
        return $this->tax->approveRate($code, $attributes);
    }

    public function createOpeningBatch(Company $company, AccountingBook $book, string $asOf, ?string $currency = null): OpeningBalanceBatch
    {
        return $this->openingBalances->createDraft($company, $book, $asOf, $currency);
    }

    /** @param array{account: string, debit?: string|float|int, credit?: string|float|int, currency?: string, exchange_rate?: float|int|string} $line */
    public function addOpeningLine(OpeningBalanceBatch $batch, array $line): OpeningBalanceBatch
    {
        return $this->openingBalances->addLine($batch, $line);
    }

    /** @return LengthAwarePaginator<int, OpeningBalanceBatch> */
    public function listOpeningBalances(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            OpeningBalanceBatch::query()->where('company_id', $company->id)->where('book_id', $book->id),
            $request,
            ['id', 'as_of', 'status', 'created_at'],
            ['status' => 'status'],
            'as_of',
        );
    }

    /**
     * @param  list<array{account: string, debit?: string|float|int, credit?: string|float|int}>  $lines
     */
    public function postOpeningBalances(Company $company, AccountingBook $book, string $asOf, array $lines): mixed
    {
        return $this->openingBalances->post($company, $book, $asOf, $lines);
    }

    public function validateOpeningBatch(OpeningBalanceBatch $batch): OpeningBalanceBatch
    {
        return $this->openingBalances->validate($batch);
    }

    public function postOpeningBatch(OpeningBalanceBatch $batch): OpeningBalanceBatch
    {
        return $this->openingBalances->postBatch($batch);
    }

    public function lockOpeningBatch(OpeningBalanceBatch $batch): OpeningBalanceBatch
    {
        return $this->openingBalances->lock($batch);
    }

    /** @return array<int, AccountingBook> */
    public function listBooks(Company $company): array
    {
        return AccountingBook::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get()
            ->all();
    }

    /** @return array<string, mixed> */
    public function reconcileBooks(Company $company, AccountingBook $left, AccountingBook $right): array
    {
        return $this->multiBook->reconcile($company, $left, $right);
    }

    /** @return array<string, mixed> */
    public function managementPack(Company $company, AccountingBook $book, ?string $asOf = null): array
    {
        return $this->reporting->managementPack(
            $company,
            $book,
            new ReportRequest(companyId: $company->id, bookId: $book->id, asOf: $asOf),
        );
    }

    /** @return array<string, mixed> */
    public function cashFlow(Company $company, AccountingBook $book, ?string $asOf = null): array
    {
        $pack = $this->managementPack($company, $book, $asOf);

        return $pack['cash_flow'] ?? $pack;
    }

    /** @return array<string, mixed> */
    public function otherComprehensiveIncome(Company $company, AccountingBook $book, ?string $asOf = null): array
    {
        return $this->reporting->otherComprehensiveIncome($company, $book, $asOf !== null ? Carbon::parse($asOf) : null);
    }

    /** @return array<string, mixed> */
    public function cashForecast(Company $company, AccountingBook $book, ?string $asOf = null): array
    {
        return $this->reporting->cashForecast($company, $asOf !== null ? Carbon::parse($asOf) : null, $book);
    }

    /**
     * Budget vs actual per budget line. Each line's variance is computed by the
     * domain BudgetService (no accounting logic here).
     *
     * @return list<array<string, mixed>>
     */
    public function budgetVsActual(Company $company, AccountingBook $book, ?string $asOf = null): array
    {
        $when = $asOf !== null ? Carbon::parse($asOf) : Carbon::now();
        $rows = [];

        foreach (BudgetLine::query()->where('company_id', $company->id)->where('book_id', $book->id)->orderBy('account_code')->get() as $line) {
            $v = $this->budgets->variance($company, $book, (string) $line->period_key, (string) $line->account_code, $when);
            $rows[] = [
                'period_key' => $line->period_key,
                'account_code' => $line->account_code,
                'budget' => (string) $v['budget'],
                'actual' => (string) $v['actual'],
                'variance' => (string) $v['variance'],
            ];
        }

        return $rows;
    }

    public function setBudgetLine(Company $company, AccountingBook $book, string $periodKey, string $accountCode, string|float|int $amount): BudgetLine
    {
        return $this->budgets->set($company, $book, $periodKey, $accountCode, $amount);
    }
}
