<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use App\Models\Tax\TaxCode;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SalesInvoiceCreate extends Component
{
    use InteractsWithAccountingContext;

    public ?int $customer_id = null;

    public string $invoice_date = '';

    /** @var array<int, array{description: string, revenue_account: string, tax_code: string, quantity: string, unit_price: string}> */
    public array $lines = [];

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->lines = [
            ['description' => '', 'revenue_account' => '410101', 'tax_code' => '', 'quantity' => '1', 'unit_price' => '0'],
        ];
    }

    public function addLine(): void
    {
        $this->lines[] = ['description' => '', 'revenue_account' => '410101', 'tax_code' => '', 'quantity' => '1', 'unit_price' => '0'];
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

    public function save(ArApplicationService $ar): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();

        $this->validate([
            'customer_id' => 'required|integer',
            'invoice_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.revenue_account' => 'required|string',
            'lines.*.tax_code' => ['nullable', 'string', Rule::exists('tax_codes', 'code')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_active', true)->whereIn('kind', ['output', 'output_vat']))],
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_price' => 'required|numeric|gt:0',
        ]);

        $customer = $ar->findCustomer($company, (int) $this->customer_id);

        $payload = [];
        foreach ($this->lines as $line) {
            $payloadLine = [
                'revenue_account' => $line['revenue_account'],
                'description' => $line['description'] ?: null,
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ];
            if ($line['tax_code'] !== '') {
                $payloadLine['tax_code'] = $line['tax_code'];
            }
            $payload[] = $payloadLine;
        }

        $ar->createInvoiceDraft($company, $book, $customer, $payload, [
            'invoice_date' => $this->invoice_date,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', __('erp.success_created'));
        $this->redirectRoute('ar.invoices', navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $customers = $company
            ? Customer::query()->where('company_id', $company->id)->orderBy('code')->get()
            : collect();
        $taxCodes = $company
            ? TaxCode::query()->where('company_id', $company->id)->where('is_active', true)->whereIn('kind', ['output', 'output_vat'])->orderBy('code')->get(['code', 'name'])
            : collect();

        return view('livewire.ar.sales-invoice-create', compact('customers', 'taxCodes'));
    }
}
