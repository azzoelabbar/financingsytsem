<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Ar\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReceiptCreate extends Component
{
    use InteractsWithAccountingContext;

    public ?int $customer_id = null;

    public string $receipt_date = '';

    public string $amount = '';

    public string $currency = 'LYD';

    public string $exchange_rate = '1';

    public string $method = 'bank';

    public string $cash_bank_account = '110102';

    public string $reference = '';

    public function mount(): void
    {
        $this->receipt_date = now()->toDateString();
    }

    public function updatedCustomerId(): void
    {
        $company = $this->company();
        if ($company === null || $this->customer_id === null) {
            return;
        }

        $customer = Customer::query()
            ->where('company_id', $company->id)
            ->find($this->customer_id);

        if ($customer !== null) {
            $this->currency = $customer->currency;
        }
    }

    public function save(ArApplicationService $ar): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();

        $this->validate([
            'customer_id' => 'required|integer',
            'receipt_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|gt:0',
            'method' => ['required', Rule::in(['bank', 'cash'])],
            'cash_bank_account' => [
                'required',
                'string',
                Rule::exists('accounts', 'code')->where(fn ($query) => $query
                    ->where('company_id', $company->id)
                    ->where('is_posting', true)
                    ->where('is_active', true)),
            ],
            'reference' => 'nullable|string|max:255',
        ]);

        $customer = $ar->findCustomer($company, (int) $this->customer_id);
        $receipt = $ar->createReceiptDraft($company, $book, $customer, $this->amount, [
            'receipt_date' => $this->receipt_date,
            'currency' => strtoupper($this->currency),
            'exchange_rate' => $this->exchange_rate,
            'method' => $this->method,
            'cash_bank_account' => $this->cash_bank_account,
            'reference' => $this->reference !== '' ? $this->reference : null,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', __('erp.receipt.created_success'));
        $this->redirectRoute('ar.receipts.show', $receipt->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $customers = $company
            ? Customer::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get()
            : collect();
        $accounts = $company
            ? Account::query()
                ->where('company_id', $company->id)
                ->where('is_posting', true)
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query->where('is_bank_account', true)->orWhere('code', 'like', '1101%');
                })
                ->orderBy('code')
                ->get()
            : collect();

        return view('livewire.ar.receipt-create', compact('customers', 'accounts'));
    }
}
