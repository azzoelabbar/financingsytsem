<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\Supplier;
use App\Models\Tax\TaxCode;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PurchaseInvoiceCreate extends Component
{
    use InteractsWithAccountingContext;

    public ?int $supplier_id = null;

    public string $invoice_date = '';

    /** @var array<int, array{description: string, expense_account: string, tax_code: string, quantity: string, unit_price: string}> */
    public array $lines = [];

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->lines = [
            ['description' => '', 'expense_account' => '510101', 'tax_code' => '', 'quantity' => '1', 'unit_price' => '0'],
        ];
    }

    public function addLine(): void
    {
        $this->lines[] = ['description' => '', 'expense_account' => '510101', 'tax_code' => '', 'quantity' => '1', 'unit_price' => '0'];
    }

    /** Drop a line from the draft. The last remaining line is kept so the form always has one row. */
    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 1) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save(ApApplicationService $ap): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();

        $this->validate([
            'supplier_id' => 'required|integer',
            'invoice_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.expense_account' => 'required|string',
            'lines.*.tax_code' => ['nullable', 'string', Rule::exists('tax_codes', 'code')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_active', true)->whereIn('kind', ['input', 'input_vat']))],
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_price' => 'required|numeric|gt:0',
        ]);

        $supplier = $ap->findSupplier($company, (int) $this->supplier_id);

        $payload = [];
        foreach ($this->lines as $line) {
            $payloadLine = [
                'expense_account' => $line['expense_account'],
                'description' => $line['description'] ?: null,
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ];
            if ($line['tax_code'] !== '') {
                $payloadLine['tax_code'] = $line['tax_code'];
            }
            $payload[] = $payloadLine;
        }

        $invoice = $ap->createInvoiceDraft($company, $book, $supplier, $payload, [
            'invoice_date' => $this->invoice_date,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', __('erp.success_created'));
        $this->redirectRoute('ap.invoices.show', $invoice->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $suppliers = $company
            ? Supplier::query()->where('company_id', $company->id)->orderBy('code')->get()
            : collect();
        $taxCodes = $company
            ? TaxCode::query()->where('company_id', $company->id)->where('is_active', true)->whereIn('kind', ['input', 'input_vat'])->orderBy('code')->get(['code', 'name'])
            : collect();

        return view('livewire.ap.purchase-invoice-create', compact('suppliers', 'taxCodes'));
    }
}
