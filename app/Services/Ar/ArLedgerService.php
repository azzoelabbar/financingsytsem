<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\Customer;
use App\Models\Ar\Receipt;
use App\Models\Ar\SalesCreditNote;
use App\Models\Ar\SalesDebitNote;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The AR subledger view (open items / aging / customer statement). Balances are
 * derived from documents + allocations — the single source of truth. Totals are
 * returned in the functional currency so they reconcile with the GL AR control.
 */
class ArLedgerService
{
    /**
     * AR subledger open total in functional currency. Must equal the GL balance
     * of the AR control account (INV-7):
     *   Σ invoice.open + Σ debit_note.open − Σ receipt.unallocated − Σ credit_note.open
     *
     * @return numeric-string
     */
    public function subledgerTotal(Company $company, ?Carbon $asOf = null, ?AccountingBook $book = null): string
    {
        $total = '0';

        foreach ($this->postedInvoices($company, $asOf, book: $book) as $invoice) {
            $total = Decimal::add($total, $this->functional($invoice->openBalance(), $invoice->effectiveFxRate()));
        }

        foreach ($this->postedDebitNotes($company, $asOf, book: $book) as $dn) {
            $total = Decimal::add($total, $this->functional($dn->openBalance(), $dn->exchange_rate));
        }

        foreach ($this->postedReceipts($company, $asOf, book: $book) as $receipt) {
            $total = Decimal::sub($total, $this->functional((string) $receipt->unallocated_amount, $receipt->exchange_rate));
        }

        foreach ($this->postedCreditNotes($company, $asOf, book: $book) as $cn) {
            $total = Decimal::sub($total, $this->functional($cn->openBalance(), $cn->exchange_rate));
        }

        return $total;
    }

    /**
     * Aging of open items (functional currency) bucketed against the as-of date.
     * Unallocated receipts and credit notes sit in `credits` so the total equals
     * the AR control (INV-7).
     *
     * @return array{buckets: array<string, numeric-string>, total: numeric-string}
     */
    public function aging(Company $company, ?Carbon $asOf = null, ?Customer $customer = null, ?AccountingBook $book = null): array
    {
        $asOf ??= Carbon::now();
        $customerId = $customer?->id;
        $buckets = ['current' => '0', '1_30' => '0', '31_60' => '0', '61_90' => '0', '90_plus' => '0', 'credits' => '0'];
        $total = '0';

        foreach ($this->postedInvoices($company, $asOf, $customerId, $book) as $invoice) {
            $open = $this->functional($invoice->openBalance(), $invoice->effectiveFxRate());
            if (Decimal::compare($open, '0') <= 0) {
                continue;
            }
            $due = Carbon::parse($invoice->due_date ?? $invoice->invoice_date);
            $this->addToBucket($buckets, $total, $open, $due, $asOf);
        }

        foreach ($this->postedDebitNotes($company, $asOf, $customerId, $book) as $dn) {
            $open = $this->functional($dn->openBalance(), $dn->exchange_rate);
            if (Decimal::compare($open, '0') <= 0) {
                continue;
            }
            $this->addToBucket($buckets, $total, $open, Carbon::parse($dn->debit_note_date), $asOf);
        }

        foreach ($this->postedReceipts($company, $asOf, $customerId, $book) as $receipt) {
            $credit = $this->functional((string) $receipt->unallocated_amount, $receipt->exchange_rate);
            if (! Decimal::isPositive($credit)) {
                continue;
            }
            $buckets['credits'] = Decimal::add($buckets['credits'], $credit);
            $total = Decimal::sub($total, $credit);
        }

        foreach ($this->postedCreditNotes($company, $asOf, $customerId, $book) as $cn) {
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
     * Open items for one customer (document currency converted to functional).
     *
     * @return list<array{type: string, id: int, number: ?string, date: string, currency: string, amount: numeric-string, open: numeric-string}>
     */
    public function openItems(Customer $customer, ?Carbon $asOf = null, ?AccountingBook $book = null): array
    {
        $company = Company::query()->findOrFail($customer->company_id);
        $items = [];

        foreach ($this->postedInvoices($company, $asOf, $customer->id, $book) as $invoice) {
            $open = $this->functional($invoice->openBalance(), $invoice->effectiveFxRate());
            if (Decimal::compare($open, '0') === 0) {
                continue;
            }
            $items[] = [
                'type' => 'invoice',
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => Carbon::parse($invoice->invoice_date)->toDateString(),
                'currency' => $invoice->currency,
                'amount' => $this->functional((string) $invoice->gross_total, $invoice->effectiveFxRate()),
                'open' => $open,
            ];
        }

        foreach ($this->postedDebitNotes($company, $asOf, $customer->id, $book) as $dn) {
            $open = $this->functional($dn->openBalance(), $dn->exchange_rate);
            if (Decimal::compare($open, '0') === 0) {
                continue;
            }
            $items[] = [
                'type' => 'debit_note',
                'id' => $dn->id,
                'number' => $dn->number,
                'date' => Carbon::parse($dn->debit_note_date)->toDateString(),
                'currency' => $dn->currency,
                'amount' => $this->functional((string) $dn->gross_total, $dn->exchange_rate),
                'open' => $open,
            ];
        }

        foreach ($this->postedReceipts($company, $asOf, $customer->id, $book) as $receipt) {
            $open = $this->functional((string) $receipt->unallocated_amount, $receipt->exchange_rate);
            if (! Decimal::isPositive($open)) {
                continue;
            }
            $items[] = [
                'type' => 'receipt',
                'id' => $receipt->id,
                'number' => $receipt->number,
                'date' => Carbon::parse($receipt->receipt_date)->toDateString(),
                'currency' => $receipt->currency,
                'amount' => $this->functional((string) $receipt->amount, $receipt->exchange_rate),
                'open' => Decimal::sub('0', $open),
            ];
        }

        foreach ($this->postedCreditNotes($company, $asOf, $customer->id, $book) as $cn) {
            $open = $this->functional($cn->openBalance(), $cn->exchange_rate);
            if (! Decimal::isPositive($open)) {
                continue;
            }
            $items[] = [
                'type' => 'credit_note',
                'id' => $cn->id,
                'number' => $cn->number,
                'date' => Carbon::parse($cn->credit_note_date)->toDateString(),
                'currency' => $cn->currency,
                'amount' => $this->functional((string) $cn->gross_total, $cn->exchange_rate),
                'open' => Decimal::sub('0', $open),
            ];
        }

        return $items;
    }

    /**
     * Customer statement: open items + aging + running balance (functional).
     *
     * @return array{
     *     customer: array{id: int, code: string, name_ar: string, currency: string},
     *     open_items: list<array{type: string, id: int, number: ?string, date: string, currency: string, amount: numeric-string, open: numeric-string}>,
     *     aging: array{buckets: array<string, numeric-string>, total: numeric-string},
     *     balance: numeric-string
     * }
     */
    public function statement(Customer $customer, ?Carbon $asOf = null, ?AccountingBook $book = null): array
    {
        $company = Company::query()->findOrFail($customer->company_id);
        $items = $this->openItems($customer, $asOf, $book);
        $balance = '0';
        foreach ($items as $item) {
            $balance = Decimal::add($balance, $item['open']);
        }

        return [
            'customer' => [
                'id' => $customer->id,
                'code' => $customer->code,
                'name_ar' => $customer->name_ar,
                'currency' => $customer->currency,
            ],
            'open_items' => $items,
            'aging' => $this->aging($company, $asOf, $customer, $book),
            'balance' => $balance,
        ];
    }

    /**
     * @return Collection<int, SalesInvoice>
     */
    private function postedInvoices(Company $company, ?Carbon $asOf, ?int $customerId = null, ?AccountingBook $book = null): Collection
    {
        return SalesInvoice::query()
            ->with('writeoffs')
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('invoice_date', '<=', $asOf))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->get();
    }

    /**
     * @return Collection<int, SalesDebitNote>
     */
    private function postedDebitNotes(Company $company, ?Carbon $asOf, ?int $customerId = null, ?AccountingBook $book = null): Collection
    {
        return SalesDebitNote::query()
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('debit_note_date', '<=', $asOf))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->get();
    }

    /**
     * @return Collection<int, Receipt>
     */
    private function postedReceipts(Company $company, ?Carbon $asOf, ?int $customerId = null, ?AccountingBook $book = null): Collection
    {
        return Receipt::query()
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('receipt_date', '<=', $asOf))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->get();
    }

    /**
     * @return Collection<int, SalesCreditNote>
     */
    private function postedCreditNotes(Company $company, ?Carbon $asOf, ?int $customerId = null, ?AccountingBook $book = null): Collection
    {
        return SalesCreditNote::query()
            ->where('company_id', $company->id)
            ->when($book, fn ($q) => $q->where('book_id', $book->id))
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('credit_note_date', '<=', $asOf))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
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
            default => '90_plus',
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
