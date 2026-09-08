<?php

declare(strict_types=1);

namespace App\Livewire\Imports;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Inventory\InventoryItem;
use App\Services\Inventory\InventoryService;
use App\Support\Accounting\AccountOptions;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.erp')]
class ItemForm extends Component
{
    use InteractsWithAccountingContext;

    #[Locked]
    public ?int $itemId = null;

    /** Set once an item has stock movements: the code is a posting reference by then. */
    #[Locked]
    public bool $codeLocked = false;

    public string $code = '';

    public string $name = '';

    public string $category = '';

    public string $unit = '';

    public string $gl_account_code = '';

    public string $cogs_account_code = '';

    public string $standard_cost = '';

    public string $sale_price = '';

    public string $reorder_level = '';

    public function mount(?InventoryItem $item = null): void
    {
        abort_unless($this->context()->can('gl.write'), 403);
        // The create route binds no model, so Livewire hands over an unsaved instance.
        if ($item === null || ! $item->exists) {
            return;
        }
        abort_unless((int) $item->company_id === $this->requireCompany()->id, 404);
        $this->itemId = (int) $item->id;
        $this->codeLocked = $item->moves()->exists();
        $this->code = (string) $item->code;
        $this->name = (string) $item->name;
        $this->category = (string) ($item->category ?? '');
        $this->unit = (string) ($item->unit ?? '');
        $this->gl_account_code = (string) $item->gl_account_code;
        $this->cogs_account_code = (string) $item->cogs_account_code;
        $this->standard_cost = $this->amount($item->standard_cost);
        $this->sale_price = $this->amount($item->sale_price);
        $this->reorder_level = $this->amount($item->reorder_level);
    }

    public function save(InventoryService $inventory): void
    {
        abort_unless($this->context()->can('gl.write'), 403);
        $company = $this->requireCompany();
        $item = $this->item();
        $unique = Rule::unique('inventory_items', 'code')->where('company_id', $company->id);
        if ($item !== null) {
            $unique->ignore($item->id);
        }
        $this->validate([
            'code' => $this->codeLocked ? 'nullable' : ['required', 'string', 'max:50', $unique],
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'gl_account_code' => ['required', 'string', $this->accountRule('inventory')],
            'cogs_account_code' => ['required', 'string', $this->accountRule('general')],
            'standard_cost' => 'nullable|numeric|min:0|max:999999999999',
            'sale_price' => 'nullable|numeric|min:0|max:999999999999',
            'reorder_level' => 'nullable|numeric|min:0|max:999999999999',
        ]);

        $data = [
            'name' => $this->name,
            'category' => $this->blank($this->category),
            'unit' => $this->blank($this->unit),
            'gl_account_code' => $this->gl_account_code,
            'cogs_account_code' => $this->cogs_account_code,
            'standard_cost' => $this->blank($this->standard_cost),
            'sale_price' => $this->blank($this->sale_price),
            'reorder_level' => $this->blank($this->reorder_level),
        ];
        if (! $this->codeLocked) {
            $data['code'] = $this->code;
        }
        if ($item === null) {
            $inventory->defineItem($company, $data);
        } else {
            $inventory->updateItem($item, $data);
        }

        session()->flash('success', $item === null ? __('erp.success_created') : __('imports.item_updated'));
        $this->redirectRoute('imports.inventory', navigate: true);
    }

    private function item(): ?InventoryItem
    {
        if ($this->itemId === null) {
            return null;
        }

        return InventoryItem::query()->where('company_id', $this->requireCompany()->id)->findOrFail($this->itemId);
    }

    /** Reject an account the picker would never have offered, and name the ones it does. */
    private function accountRule(string $purpose): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($purpose): void {
            $company = $this->requireCompany();
            if (! is_string($value) || ! AccountOptions::for($company, $purpose)->where('code', $value)->exists()) {
                $fail(__('imports.account_missing', ['code' => is_string($value) ? $value : '', 'accounts' => AccountOptions::summary($company, $purpose)]));
            }
        };
    }

    private function blank(string $value): ?string
    {
        return trim($value) === '' ? null : trim($value);
    }

    private function amount(?string $value): string
    {
        return $value === null ? '' : rtrim(rtrim($value, '0'), '.');
    }

    public function render(): View
    {
        abort_unless($this->context()->can('gl.write'), 403);
        $company = $this->requireCompany();

        return view('livewire.imports.item-form', [
            'inventoryAccounts' => AccountOptions::for($company, 'inventory')->get(),
            'cogsAccounts' => AccountOptions::for($company, 'general')->get(),
            'editing' => $this->itemId !== null,
        ]);
    }
}
