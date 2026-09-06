<?php

declare(strict_types=1);

namespace App\Services\Ap;

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\PurchaseCreditNote;
use App\Models\Ap\PurchaseDebitNote;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\Supplier;
use App\Models\Ap\SupplierPayment;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * AP subledger view (open items / aging / supplier statement). Totals are in
 * functional currency so they reconcile with the GL AP control (INV-8).
 *
 *   Σ invoice.open + Σ debit_note.open − Σ payment.unallocated − Σ credit_note.open
 */
class ApLedgerService
{
    /**
     * @return numeric-string
     */
    public function subledgerTotal(Company $company, ?Carbon $asOf = null, ?int $supplierId = null, ?AccountingBook $book = null): string
    {
        $total = '0';

        foreach ($this->postedInvoices($company, $asOf, $supplierId, $book) as $invoice) {
            $total = Decimal::add($total, $this->functional($invoice->openBalance(), $invoice->effectiveFxRate()));
        }
        foreach ($this->postedDebitNotes($company, $asOf, $supplierId, $book) as $dn) {
            $total = Decimal::add($total, $this->functional($dn->openBalance(), $dn->exchange_rate));
        }
        foreach ($this->postedPayments($company, $asOf, $supplierId, $book) as $payment) {
            $total = Decimal::sub($total, $this->functional((string) $payment->unallocated_amount, $payment->exchange_rate));
        }
        foreach ($this->postedCreditNotes($company, $asOf, $supplierId, $book) as $cn) {
            $total = Decimal::sub($total, $this->functional($cn->openBalance(), $cn->exchange_rate));
        }

        return $total;
    }

    /**
     * @return array{buckets: array<string, numeric-string>, total: numeric-string}
     */
    public function aging(Company $company, ?Carbon $asOf = null, ?Supplier $supplier = null, ?AccountingBook $book = null): array
    {
        $asOf ??= Carbon::now();
        $supplierId = $supplier?->id;
        $buckets = ['current' => '0', '1_30' => '0', '31_60' => '0', '61_90' => '0', '91_120' => '0', '120_plus' => '0', 'credits' => '0'];
        $total = '0';

        foreach ($this->postedInvoices($company, $asOf, $supplierId, $book) as $invoice) {
            $open = $this->functional($invoice->openBalance(), $invoice->effectiveFxRate());
            if (Decimal::compare($open, '0') <= 0) {
                continue;
            }
            $due = Carbon::parse($invoice->due_date ?? $invoice->invoice_date);
            $this->addToBucket($buckets, $total, $open, $due, $asOf);
        }

        foreach ($this->postedDebitNotes($company, $asOf, $supplierId, $book) as $dn) {
            $open = $this->functional($dn->openBalance(), $dn->exchange_rate);
            if (Decimal::compare($open, '0') <= 0) {
                continue;
            }
            $this->addToBucket($buckets, $total, $open, Carbon::parse($dn->debit_note_date), $asOf);
        }

        foreach ($this->postedPayments($company, $asOf, $supplierId, $book) as $payment) {
            $credit = $this->functional((string) $payment->unallocated_amount, $payment->exchange_rate);
            if (! Decimal::isPositive($credit)) {
                continue;
            }
            $buckets['credits'] = Decimal::add($buckets['credits'], $credit);
            $total = Decimal::sub($total, $credit);
        }

        foreach ($this->postedCreditNotes($company, $asOf, $supplierId, $book) as $cn) {
            $credit = $this->functional($cn->openBalance(), $cn->exchange_rate);
            if (! Decimal::isPositive($credit)) {
                continue;
            }
            $buckets['credits'] = Decimal::add($buckets['credits'], $credit);
            $total = Decimal::sub($total, $credit);
        }

        return ['buckets' => $buckets, 'total' => $total];
    }

    /**
     * @return list<array{type: string, id: int, number: ?string, date: string, due_date: ?string, days_outstanding: int, currency: string, amount: numeric-string, open: numeric-string}>
     */
    public function openItems(Supplier $supplier, ?Carbon $asOf = null, ?AccountingBook $book = null): array
    {
        $company = Company::query()->findOrFail($supplier->company_id);
        $asOf ??= Carbon::now();
        $items = [];

        foreach ($this->postedInvoices($company, $asOf, $supplier->id, $book) as $invoice) {
            $open = $this->functional($invoice->openBalance(), $invoice->effectiveFxRate());
            if (Decimal::compare($open, '0') === 0) {
                continue;
            }
            $due = Carbon::parse($invoice->due_date ?? $invoice->invoice_date);
            $items[] = [
                'type' => 'invoice',
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => Carbon::parse($invoice->invoice_date)->toDateString(),
                'due_date' => $invoice->due_date !== null ? Carbon::parse($invoice->due_date)->toDateString() : null,
                'days_outstanding' => (int) $due->diffInDays($asOf, false),
                'currency' => $invoice->currency,
                'amount' => $this->functional((string) $invoice->gross_total, $invoice->effectiveFxRate()),
                'open' => $open,
            ];
        }

        foreach ($this->postedDebitNotes($company, $asOf, $supplier->id, $book) as $dn) {
            $open = $this->functional($dn->openBalance(), $dn->exchange_rate);
            if (Decimal::compare($open, '0') === 0) {
                continue;
            }
            $date = Carbon::parse($dn->debit_note_date);
            $items[] = [
                'type' => 'debit_note',
                'id' => $dn->id,
                'number' => $dn->number,
                'date' => $date->toDateString(),
                'due_date' => $date->toDateString(),
                'days_outstanding' => (int) $date->diffInDays($asOf, false),
                'currency' => $dn->currency,
                'amount' => $this->functional((string) $dn->gross_total, $dn->exchange_rate),
                'open' => $open,
            ];
        }

        foreach ($this->postedPayments($company, $asOf, $supplier->id, $book) as $payment) {
            $open = $this->functional((string) $payment->unallocated_amount, $payment->exchange_rate);
            if (! Decimal::isPositive($open)) {
                continue;
            }
            $items[] = [
                'type' => 'payment',
                'id' => $payment->id,
                'number' => $payment->number,
                'date' => Carbon::parse($payment->payment_date)->toDateString(),
                'due_date' => null,
                'days_outstanding' => 0,
                'currency' => $payment->currency,
                'amount' => $this->functional((string) $payment->amount, $payment->exchange_rate),
                'open' => Decimal::sub('0', $open),
            ];
        }

        foreach ($this->postedCreditNotes($company, $asOf, $supplier->id, $book) as $cn) {
            $open = $this->functional($cn->openBalance(), $cn->exchange_rate);
            if (! Decimal::isPositive($open)) {
                continue;
            }
            $items[] = [
                'type' => 'credit_note',
                'id' => $cn->id,
                'number' => $cn->number,
                'date' => Carbon::parse($cn->credit_note_date)->toDateString(),
                'due_date' => null,
                'days_outstanding' => 0,
                'currency' => $cn->currency,
                'amount' => $this->functional((string) $cn->gross_total, $cn->exchange_rate),
                'open' => Decimal::sub('0', $open),
            ];
        }

        return $items;
    }

    /**
     * @return array{
     *     supplier: array{id: int, code: string, legal_name: string, currency: string},
     *     opening_balance: numeric-string,
     *     movements: list<array{type: string, date: string, number: ?string, debit: numeric-string, credit: numeric-string}>,
     *     closing_balance: numeric-string,
     *     open_items: list<array{type: string, id: int, number: ?string, date: string, due_date: ?string, days_outstanding: int, currency: string, amount: numeric-string, open: numeric-string}>,
     *     aging: array{buckets: array<string, numeric-string>, total: numeric-string}
     * }
     */
    public function statement(Supplier $supplier, ?Carbon $from = null, ?Carbon $to = null, ?AccountingBook $book = null): array
    {
        $company = Company::query()->findOrFail($supplier->company_id);
        $openingAsOf = $from?->copy()->subDay();
        $opening = $openingAsOf === null ? '0' : $this->subledgerTotal($company, $openingAsOf, $supplier->id, $book);

        $movements = [];
        foreach ($this->postedInvoices($company, $to, $supplier->id, $book) as $invoice) {
            if ($from !== null && Carbon::parse($invoice->invoice_date)->lt($from)) {
                continue;
            }
            $amt = $this->functional((string) $invoice->gross_total, $invoice->effectiveFxRate());
            $movements[] = [
                'type' => 'invoice',
                'date' => Carbon::parse($invoice->invoice_date)->toDateString(),
                'number' => $invoice->number,
                'debit' => '0',
                'credit' => $amt,
            ];
        }
        foreach ($this->postedDebitNotes($company, $to, $supplier->id, $book) as $dn) {
            if ($from !== null && Carbon::parse($dn->debit_note_date)->lt($from)) {
                continue;
            }
            $amt = $this->functional((string) $dn->gross_total, $dn->exchange_rate);
            $movements[] = [
                'type' => 'debit_note',
                'date' => Carbon::parse($dn->debit_note_date)->toDateString(),
                'number' => $dn->number,
                'debit' => '0',
                'credit' => $amt,
            ];
        }
        foreach ($this->postedCreditNotes($company, $to, $supplier->id, $book) as $cn) {
            if ($from !== null && Carbon::parse($cn->credit_note_date)->lt($from)) {
                continue;
            }
            $amt = $this->functional((string) $cn->gross_total, $cn->exchange_rate);
            $movements[] = [
                'type' => 'credit_note',
                'date' => Carbon::parse($cn->credit_note_date)->toDateString(),
                'number' => $cn->number,
                'debit' => $amt,
                'credit' => '0',
            ];
        }
        foreach ($this->postedPayments($company, $to, $supplier->id, $book) as $payment) {
            if ($from !== null && Carbon::parse($payment->payment_date)->lt($from)) {
                continue;
            }
            $amt = $this->functional((string) $payment->amount, $payment->exchange_rate);
            $movements[] = [
                'type' => 'payment',
                'date' => Carbon::parse($payment->payment_date)->toDateString(),
                'number' => $payment->number,
                'debit' => $amt,
                'credit' => '0',
            ];
        }

        usort($movements, fn (array $a, array $b): int => strcmp($a['date'], $b['date']));

        $closing = $this->subledgerTotal($company, $to, $supplier->id, $book);

        return [
            'supplier' => [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'legal_name' => $supplier->legal_name,
                'currency' => $supplier->currency,
            ],
            'opening_balance' => $opening,
            'movements' => $movements,
            'closing_balance' => $closing,
            'open_items' => $this->openItems($supplier, $to, $book),
            'aging' => $this->aging($company, $to, $supplier, $book),
        ];
    }

    /**
     * @return Collection<int, PurchaseInvoice>
     */
    private function postedInvoices(Company $company, ?Carbon $asOf, ?int $supplierId = null, ?AccountingBook $book = null): Collection
    {
        return PurchaseInvoice::query()
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('invoice_date', '<=', $asOf))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->get();
    }

    /**
     * @return Collection<int, PurchaseDebitNote>
     */
    private function postedDebitNotes(Company $company, ?Carbon $asOf, ?int $supplierId = null, ?AccountingBook $book = null): Collection
    {
        return PurchaseDebitNote::query()
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('debit_note_date', '<=', $asOf))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->get();
    }

    /**
     * @return Collection<int, SupplierPayment>
     */
    private function postedPayments(Company $company, ?Carbon $asOf, ?int $supplierId = null, ?AccountingBook $book = null): Collection
    {
        return SupplierPayment::query()
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('payment_date', '<=', $asOf))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->get();
    }

    /**
     * @return Collection<int, PurchaseCreditNote>
     */
    private function postedCreditNotes(Company $company, ?Carbon $asOf, ?int $supplierId = null, ?AccountingBook $book = null): Collection
    {
        return PurchaseCreditNote::query()
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('credit_note_date', '<=', $asOf))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->get();
    }

    /**
     * @param  array<string, numeric-string>  $buckets
     * @param  numeric-string  $total
     * @param  numeric-string  $open
     */
    private function addToBucket(array &$buckets, string &$total, string $open, Carbon $due, Carbon $asOf): void
    {
        $days = $due->copy()->startOfDay()->diffInDays($asOf->copy()->startOfDay(), false);
        $bucket = match (true) {
            $days <= 0 => 'current',
            $days <= 30 => '1_30',
            $days <= 60 => '31_60',
            $days <= 90 => '61_90',
            $days <= 120 => '91_120',
            default => '120_plus',
        };
        $buckets[$bucket] = Decimal::add($buckets[$bucket], $open);
        $total = Decimal::add($total, $open);
    }

    /**
     * @return numeric-string
     */
    private function functional(string $amount, mixed $rate): string
    {
        return Decimal::mul(Decimal::of($amount), Decimal::of(is_scalar($rate) ? (string) $rate : '1'));
    }
}
