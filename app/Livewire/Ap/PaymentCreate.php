<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Ap\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PaymentCreate extends Component
{
    use InteractsWithAccountingContext;

    public ?int $supplier_id = null;

    public string $payment_date = '';

    public string $amount = '';

    public string $currency = 'LYD';

    public string $exchange_rate = '1';

    public string $method = 'bank';

    public string $cash_bank_account = '110102';

    public string $reference = '';

    public function mount(): void
    {
        $this->payment_date = now()->toDateString();
    }

    public function updatedSupplierId(): void
    {
        $company = $this->company();
        $supplier = $company && $this->supplier_id
            ? Supplier::query()->where('company_id', $company->id)->find($this->supplier_id)
            : null;
        if ($supplier !== null) {
            $this->currency = $supplier->currency;
        }
    }

    public function save(ApApplicationService $ap): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $this->validate([
            'supplier_id' => 'required|integer',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|gt:0',
            'method' => ['required', Rule::in(['bank', 'cash'])],
            'cash_bank_account' => ['required', 'string', Rule::exists('accounts', 'code')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_posting', true)->where('is_active', true))],
            'reference' => 'nullable|string|max:255',
        ]);

        $supplier = $ap->findSupplier($company, (int) $this->supplier_id);
        $payment = $ap->createPaymentDraft($company, $book, $supplier, $this->amount, [
            'payment_date' => $this->payment_date,
            'currency' => strtoupper($this->currency),
            'exchange_rate' => $this->exchange_rate,
            'method' => $this->method,
            'cash_bank_account' => $this->cash_bank_account,
            'reference' => $this->reference !== '' ? $this->reference : null,
            'created_by' => auth()->id(),
        ]);
        session()->flash('success', __('erp.payment.created_success'));
        $this->redirectRoute('ap.payments.show', $payment->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $suppliers = $company ? Supplier::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get() : collect();
        $accounts = $company ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->where('is_active', true)
            ->where(fn ($query) => $query->where('is_bank_account', true)->orWhere('code', 'like', '1101%'))->orderBy('code')->get() : collect();

        return view('livewire.ap.payment-create', compact('suppliers', 'accounts'));
    }
}
