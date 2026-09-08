<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Journal;
use App\Models\Assets\FixedAsset;
use App\Services\Accounting\Exceptions\PostingException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class FixedAssetShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public FixedAsset $asset;

    public string $actionDate = '';

    public string $disposalProceeds = '';

    public function mount(FixedAsset $asset): void
    {
        $company = $this->company();
        if ($company === null || $asset->company_id !== $company->id) {
            abort(404);
        }
        $this->asset = $asset;
        $this->actionDate = now()->toDateString();
    }

    public function depreciate(DomainApplicationService $service): void
    {
        $this->validate(['actionDate' => 'required|date']);
        try {
            $service->depreciateAsset($this->asset, $this->actionDate);
            $this->asset->refresh();
            session()->flash('success', __('erp.assets.depreciated'));
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function dispose(DomainApplicationService $service): void
    {
        $this->validate(['actionDate' => 'required|date', 'disposalProceeds' => 'required|numeric|min:0']);
        try {
            $service->disposeAsset($this->asset, $this->actionDate, $this->disposalProceeds);
            $this->asset->refresh();
            session()->flash('success', __('erp.assets.disposed'));
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $journals = Journal::query()
            ->where('company_id', $this->asset->company_id)
            ->where('book_id', $this->asset->book_id)
            ->where('source', 'fixed_asset')
            ->where('reference', $this->asset->code)
            ->orderBy('posting_date')
            ->orderBy('id')
            ->get();

        return view('livewire.assets.fixed-asset-show', [
            'asset' => $this->asset,
            'journals' => $journals,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.assets.title').' '.$this->asset->code;
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $meta = $this->excelMeta([
            __('erp.code') => $this->asset->code,
            __('erp.assets.name') => $this->asset->name,
            __('erp.assets.in_service') => $this->exportDate($this->asset->in_service_date),
            __('erp.assets.cost') => (string) $this->asset->cost,
            __('erp.assets.accum_dep') => (string) $this->asset->accum_depreciation,
            __('erp.assets.nbv') => $this->asset->netBookValue(),
            __('erp.status') => $this->statusLabel($this->asset->status),
        ]);

        $journals = Journal::query()
            ->where('company_id', $this->asset->company_id)
            ->where('book_id', $this->asset->book_id)
            ->where('source', 'fixed_asset')
            ->where('reference', $this->asset->code)
            ->orderBy('posting_date')
            ->orderBy('id')
            ->get();

        return [$this->excelSheetFrom(
            __('erp.assets.movement_journals'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($j) => $j->number],
                [__('erp.journal.journal_date'), ExcelSheet::DATE, fn ($j) => $this->exportDate($j->journal_date)],
                [__('erp.debit'), ExcelSheet::MONEY, fn ($j) => $j->total_debit],
                [__('erp.credit'), ExcelSheet::MONEY, fn ($j) => $j->total_credit],
                [__('erp.status'), ExcelSheet::TEXT, fn ($j) => $this->statusLabel($j->status)],
            ],
            $journals,
            $meta,
            heading: __('erp.assets.title').' '.$this->asset->code,
        )];
    }
}
