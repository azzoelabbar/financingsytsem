<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class Statement extends Component
{
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
}
