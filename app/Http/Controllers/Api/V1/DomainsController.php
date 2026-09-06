<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Api\Other\DomainApplicationService;
use App\Models\Accounting\AccountingBook;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DomainsController extends ApiController
{
    public function __construct(
        AccessControl $access,
        private readonly DomainApplicationService $domains,
    ) {
        parent::__construct($access);
    }

    public function investmentsIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::INVESTMENTS_READ);

        return $this->paginated($this->domains->listInvestments($this->company($request), $request));
    }

    public function investmentsStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::INVESTMENTS_WRITE);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'classification' => ['required', 'string'],
            'cost' => ['required', 'numeric'],
            'date' => ['required', 'date'],
            'quantity' => ['nullable', 'numeric'],
            'unit_cost' => ['nullable', 'numeric'],
            'effective_interest_rate' => ['nullable', 'numeric'],
        ]);

        return $this->created($this->domains->acquireInvestment($this->company($request), $this->book($request), $data));
    }

    public function investmentsShow(Request $request, int $investment): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::INVESTMENTS_READ);

        return $this->ok($this->domains->findInvestment($this->company($request), $investment));
    }

    public function expensesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::EXPENSES_READ);

        return $this->paginated($this->domains->listExpenses($this->company($request), $this->book($request), $request));
    }

    public function expensesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::EXPENSES_WRITE);
        $data = $request->validate([
            'number' => ['required', 'string', 'max:50'],
            'claim_date' => ['required', 'date'],
            'employee_ref' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.amount' => ['required', 'numeric'],
            'lines.*.expense_account' => ['nullable', 'string'],
        ]);

        return $this->created($this->domains->createExpenseDraft(
            $this->company($request),
            $this->book($request),
            Arr::except($data, ['lines']),
            $data['lines'],
        ));
    }

    public function expensesSubmit(Request $request, int $expense): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::EXPENSES_WRITE);
        $claim = $this->domains->findExpense($this->company($request), $this->book($request), $expense);

        return $this->ok($this->domains->submitExpense($claim));
    }

    public function expensesApprove(Request $request, int $expense): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::EXPENSES_WRITE);
        $claim = $this->domains->findExpense($this->company($request), $this->book($request), $expense);

        return $this->ok($this->domains->approveExpense($claim));
    }

    public function expensesPost(Request $request, int $expense): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::EXPENSES_WRITE);
        $claim = $this->domains->findExpense($this->company($request), $this->book($request), $expense);

        return $this->ok($this->domains->postExpense($claim));
    }

    public function projectsIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::PROJECTS_READ);

        return $this->paginated($this->domains->listProjects($this->company($request), $request));
    }

    public function projectsStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::PROJECTS_WRITE);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric'],
        ]);

        return $this->created($this->domains->defineProject($this->company($request), $data));
    }

    public function projectsCharge(Request $request, int $project): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::PROJECTS_WRITE);
        $data = $request->validate([
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric'],
        ]);
        $prj = $this->domains->findProject($this->company($request), $project);

        return $this->ok($this->domains->chargeProject($prj, $data['date'], $data['amount']));
    }

    public function projectsCapitalize(Request $request, int $project): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::PROJECTS_WRITE);
        $data = $request->validate([
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric'],
        ]);
        $prj = $this->domains->findProject($this->company($request), $project);

        return $this->ok($this->domains->capitalizeProject($prj, $data['date'], $data['amount']));
    }

    public function taxCodesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::TAX_READ);

        return $this->paginated($this->domains->listTaxCodes($this->company($request), $request));
    }

    public function taxCodesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::TAX_WRITE);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'string'],
            'gl_account_code' => ['required', 'string'],
        ]);

        return $this->created($this->domains->defineTaxCode($this->company($request), $data));
    }

    public function openingBalancesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::OPENING_BALANCES_READ);

        return $this->paginated($this->domains->listOpeningBalances($this->company($request), $this->book($request), $request));
    }

    public function openingBalancesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::OPENING_BALANCES_WRITE);
        $data = $request->validate([
            'as_of' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account' => ['required', 'string'],
            'lines.*.debit' => ['nullable', 'numeric'],
            'lines.*.credit' => ['nullable', 'numeric'],
        ]);

        return $this->created($this->domains->postOpeningBalances(
            $this->company($request),
            $this->book($request),
            $data['as_of'],
            $data['lines'],
        ));
    }

    public function booksIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::BOOKS_READ);

        return $this->ok($this->domains->listBooks($this->company($request)));
    }

    public function booksReconcile(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::BOOKS_READ);
        $data = $request->validate([
            'left_book_id' => ['required', 'integer'],
            'right_book_id' => ['required', 'integer'],
        ]);
        $company = $this->company($request);
        $left = AccountingBook::query()->where('company_id', $company->id)->where('id', $data['left_book_id'])->firstOrFail();
        $right = AccountingBook::query()->where('company_id', $company->id)->where('id', $data['right_book_id'])->firstOrFail();

        return $this->ok($this->domains->reconcileBooks($company, $left, $right));
    }
}
