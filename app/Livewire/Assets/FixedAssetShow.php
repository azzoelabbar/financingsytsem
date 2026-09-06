<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Journal;
use App\Models\Assets\FixedAsset;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class FixedAssetShow extends Component
{
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
}
