<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\Supplier;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class Statement extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    #[Url]
    public ?int $supplierId = null;

    public function render(): View
    {
        $company = $this->requireCompany();

        $suppliers = Supplier::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get(['id', 'code', 'legal_name', 'name_ar', 'trading_name']);

        $statement = null;
        if ($this->supplierId !== null) {
            $supplier = Supplier::query()->where('company_id', $company->id)->find($this->supplierId);
            if ($supplier !== null) {
                $statement = app(ApApplicationService::class)->supplierStatement($supplier, book: $this->requireBook());
            }
        }

        return view('livewire.ap.statement', [
            'suppliers' => $suppliers,
            'statement' => $statement,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.statement_page.ap_title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null || $this->supplierId === null) {
            return [];
        }

        $supplier = Supplier::query()->where('company_id', $company->id)->find($this->supplierId);

        if ($supplier === null) {
            return [];
        }

        $statement = app(ApApplicationService::class)->supplierStatement($supplier, book: $book);
        $meta = $this->excelMeta([
            __('erp.supplier.title') => $supplier->code.' - '.$supplier->legal_name,
            __('erp.statement_page.balance_due') => (string) ($statement['balance'] ?? '0'),
        ]);

        $buckets = $statement['aging']['buckets'] ?? [];
        $aging = [];

        foreach (['current', '1_30', '31_60', '61_90', '91_120', '120_plus'] as $key) {
            if (array_key_exists($key, $buckets)) {
                $aging[__('erp.aging.'.$key)] = $buckets[$key];
            }
        }

        return [
            $this->excelSheetFrom(
                __('erp.statement_page.ap_title'),
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
