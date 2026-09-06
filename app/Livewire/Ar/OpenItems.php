<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class OpenItems extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    #[Url]
    public ?int $customerId = null;

    public function render(): View
    {
        $company = $this->requireCompany();

        $customers = Customer::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'name_en']);

        $items = null;
        if ($this->customerId !== null) {
            $customer = Customer::query()->where('company_id', $company->id)->find($this->customerId);
            if ($customer !== null) {
                $items = app(ArApplicationService::class)->openItems($customer, book: $this->requireBook());
            }
        }

        return view('livewire.ar.open-items', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.open_items.ar_title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null || $this->customerId === null) {
            return [];
        }

        $customer = Customer::query()->where('company_id', $company->id)->find($this->customerId);

        if ($customer === null) {
            return [];
        }

        $items = app(ArApplicationService::class)->openItems($customer, book: $book);

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
            $items,
            $this->excelMeta([
                __('erp.customer.title') => $customer->code.' - '.($this->localisedName($customer) ?? ''),
            ]),
        )];
    }
}
