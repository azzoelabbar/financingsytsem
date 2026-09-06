<?php

declare(strict_types=1);

namespace App\Models\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesDebitNote extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'debit_note_date' => 'date',
        'document_date' => 'date',
        'exchange_rate' => 'decimal:10',
        'status' => DocumentStatus::class,
        'net_total' => 'decimal:6',
        'tax_total' => 'decimal:6',
        'gross_total' => 'decimal:6',
        'allocated_total' => 'decimal:6',
        'is_recurring' => 'boolean',
        'posted_at' => 'datetime',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<AccountingBook, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(AccountingBook::class, 'book_id');
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<SalesInvoice, $this> */
    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return HasMany<SalesDebitNoteLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(SalesDebitNoteLine::class)->orderBy('line_no');
    }

    /**
     * Outstanding amount = gross − allocated (document currency).
     *
     * @return numeric-string
     */
    public function openBalance(): string
    {
        return Decimal::sub(Decimal::of($this->gross_total ?? '0'), Decimal::of($this->allocated_total ?? '0'));
    }
}
