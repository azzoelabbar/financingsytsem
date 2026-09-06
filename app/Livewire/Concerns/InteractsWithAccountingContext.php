<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Support\Accounting\AccountingContext;
use Illuminate\Http\Request;
use Livewire\WithPagination;

trait InteractsWithAccountingContext
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    protected function context(): AccountingContext
    {
        return app(AccountingContext::class);
    }

    public function company(): ?Company
    {
        return $this->context()->company();
    }

    public function book(): ?AccountingBook
    {
        return $this->context()->book();
    }

    public function period(): ?FiscalPeriod
    {
        return $this->context()->period();
    }

    public function setCompany(int $companyId): void
    {
        $this->context()->setCompany($companyId);
        $this->resetPage();
        $this->dispatch('accounting-context-changed');
    }

    public function setBook(int $bookId): void
    {
        $this->context()->setBook($bookId);
        $this->resetPage();
        $this->dispatch('accounting-context-changed');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function listRequest(array $extra = [], ?string $searchColumns = null, ?int $perPage = null, ?int $page = null): Request
    {
        $params = array_merge([
            'search' => $this->search !== '' ? $this->search : null,
            'search_columns' => $searchColumns,
            'page' => $page ?? $this->getPage(),
            'per_page' => $perPage ?? $this->perPage,
        ], $extra);

        return Request::create('/', 'GET', array_filter(
            $params,
            static fn ($value) => $value !== null && $value !== '',
        ));
    }

    protected function requireCompany(): Company
    {
        $company = $this->company();
        if ($company === null) {
            abort(403, __('erp.no_company'));
        }

        return $company;
    }

    protected function requireBook(): AccountingBook
    {
        $book = $this->book();
        if ($book === null) {
            abort(403, __('erp.no_book'));
        }

        return $book;
    }
}
