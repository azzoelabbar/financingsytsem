<?php

declare(strict_types=1);

namespace App\Livewire\Tax;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Tax\TaxRate;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class RuleIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();

        /** @var LengthAwarePaginator<int, TaxRate>|null $rates */
        $rates = null;
        if ($company !== null) {
            $rates = TaxRate::query()
                ->whereHas('taxCode', fn ($q) => $q->where('company_id', $company->id))
                ->with('taxCode')
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->whereHas('taxCode', fn ($c) => $c->where('code', 'like', $needle)->orWhere('name', 'like', $needle));
                })
                ->orderByDesc('effective_from')
                ->paginate($this->perPage);
        }

        return view('livewire.tax.rule-index', compact('rates'));
    }

    protected function excelTitle(): string
    {
        return __('erp.tax.rules_title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $rates = TaxRate::query()
            ->whereHas('taxCode', fn ($q) => $q->where('company_id', $company->id))
            ->with('taxCode')
            ->when($this->search !== '', function ($q): void {
                $needle = '%'.$this->search.'%';
                $q->whereHas('taxCode', fn ($c) => $c->where('code', 'like', $needle)->orWhere('name', 'like', $needle));
            })
            ->orderByDesc('effective_from')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        return [$this->excelSheetFrom(
            __('erp.tax.rules_title'),
            [
                [__('erp.tax.tax_code'), ExcelSheet::TEXT, fn ($r) => $r->taxCode?->code],
                [__('erp.name'), ExcelSheet::TEXT, fn ($r) => $r->taxCode?->name],
                [__('erp.tax.rate'), ExcelSheet::NUMBER, fn ($r) => $r->rate],
                [__('erp.tax.effective_from'), ExcelSheet::DATE, fn ($r) => $this->exportDate($r->effective_from)],
                [__('erp.tax.effective_to'), ExcelSheet::DATE, fn ($r) => $this->exportDate($r->effective_to)],
                [__('erp.tax.legal_reference'), ExcelSheet::TEXT, fn ($r) => $r->legal_reference],
                [__('erp.status'), ExcelSheet::TEXT, fn ($r) => $this->statusLabel($r->status)],
            ],
            $rates,
        )];
    }
}
