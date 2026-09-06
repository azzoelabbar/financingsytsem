<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class Statement extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    #[Url]
    public ?int $customerId = null;

    public string $asOf = '';

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
    }

    public function render(): View
    {
        $company = $this->requireCompany();

        $customers = Customer::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'name_en']);

        $statement = null;
        if ($this->customerId !== null) {
            $customer = Customer::query()->where('company_id', $company->id)->find($this->customerId);
            if ($customer !== null) {
                $statement = app(ArApplicationService::class)->customerStatement($customer, Carbon::parse($this->asOf), $this->requireBook());
            }
        }

        return view('livewire.ar.statement', [
            'customers' => $customers,
            'statement' => $statement,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.statement_page.ar_title');
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

        $statement = app(ArApplicationService::class)->customerStatement($customer, Carbon::parse($this->asOf), $book);
        $meta = $this->excelMeta([
            __('erp.customer.title') => $customer->code.' - '.($this->localisedName($customer) ?? ''),
            __('erp.aging.as_of') => $this->asOf,
            __('erp.statement_page.balance_due') => (string) ($statement['balance'] ?? '0'),
        ]);

        $buckets = $statement['aging']['buckets'] ?? [];
        $aging = [];

        foreach (['current', '1_30', '31_60', '61_90', '90_plus'] as $key) {
            $aging[__('erp.aging.'.$key)] = $buckets[$key] ?? '0';
        }

        return [
            $this->excelSheetFrom(
                __('erp.statement_page.ar_title'),
                $this->openItemColumns(),
                $statement['open_items'] ?? [],
                $meta,
            ),
            $this->excelKeyValueSheet(__('erp.statement_page.aging'), $aging, meta: $meta),
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: callable(mixed): mixed}>
     */
    private function openItemColumns(): array
    {
        return [
            [__('erp.open_items.document'), ExcelSheet::TEXT, fn (array $i) => $i['number'] ?? null],
            [__('erp.open_items.doc_type'), ExcelSheet::TEXT, fn (array $i) => __('erp.open_items.types.'.$i['type'])],
            [__('erp.date'), ExcelSheet::DATE, fn (array $i) => $this->exportDate($i['date'] ?? null)],
            [__('erp.currency'), ExcelSheet::TEXT, fn (array $i) => $i['currency'] ?? null],
            [__('erp.open_items.original'), ExcelSheet::MONEY, fn (array $i) => $i['amount'] ?? null],
            [__('erp.open_items.open'), ExcelSheet::MONEY, fn (array $i) => $i['open'] ?? null],
        ];
    }
}
