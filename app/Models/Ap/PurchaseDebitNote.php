<?php

declare(strict_types=1);

namespace App\Models\Ap;

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseDebitNote extends Model
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
        'dimensions' => 'array',
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

    /** @return BelongsTo<Supplier, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** @return BelongsTo<PurchaseInvoice, $this> */
    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return HasMany<PurchaseDebitNoteLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseDebitNoteLine::class)->orderBy('line_no');
    }

    /**
     * @return numeric-string
     */
    public function openBalance(): string
    {
        return Decimal::sub(Decimal::of($this->gross_total ?? '0'), Decimal::of($this->allocated_total ?? '0'));
    }
}
