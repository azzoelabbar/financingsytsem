<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CustomerShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $company = $this->company();
        if ($company === null || $customer->company_id !== $company->id) {
            abort(404);
        }
        $this->customer = $customer;
    }

    public function render(): View
    {
        $company = $this->requireCompany();
        $book = $this->book();
        $ar = app(ArApplicationService::class);

        $statement = $book !== null ? $ar->customerStatement($this->customer, book: $book) : null;
        $invoices = null;
        $receipts = null;

        if ($book !== null) {
            $invoices = $ar->listInvoices($company, $book, Request::create('/', 'GET', [
                'customer_id' => $this->customer->id,
                'per_page' => 10,
            ]));
            $receipts = $ar->listReceipts($company, $book, Request::create('/', 'GET', [
                'customer_id' => $this->customer->id,
                'per_page' => 10,
            ]));
        }

        return view('livewire.ar.customer-show', [
            'statement' => $statement,
            'invoices' => $invoices,
            'receipts' => $receipts,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.customer.title').' '.$this->customer->code;
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $book = $this->book();

        if ($book === null) {
            return [];
        }

        $statement = app(ArApplicationService::class)->customerStatement($this->customer, book: $book);
        $meta = $this->excelMeta([
            __('erp.customer.title') => $this->customer->code.' - '.($this->localisedName($this->customer) ?? ''),
            __('erp.currency') => $this->customer->currency,
            __('erp.statement_page.balance_due') => (string) ($statement['balance'] ?? '0'),
        ]);

        return [$this->excelSheetFrom(
            __('erp.open_items.ar_title'),
            [
                [__('erp.open_items.document'), ExcelSheet::TEXT, fn (array $i) => $i['number'] ?? null],
                [__('erp.open_items.doc_type'), ExcelSheet::TEXT, fn (array $i) => __('erp.open_items.types.'.$i['type'])],
                [__('erp.date'), ExcelSheet::DATE, fn (array $i) => $this->exportDate($i['date'] ?? null)],
                [__('erp.currency'), ExcelSheet::TEXT, fn (array $i) => $i['currency'] ?? null],
                [__('erp.open_items.original'), ExcelSheet::MONEY, fn (array $i) => $i['amount'] ?? null],
                [__('erp.open_items.open'), ExcelSheet::MONEY, fn (array $i) => $i['open'] ?? null],
            ],
            $statement['open_items'] ?? [],
            $meta,
            heading: __('erp.customer.title').' '.$this->customer->code,
        )];
    }
}
