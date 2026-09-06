<?php

declare(strict_types=1);

namespace App\Models\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\User;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'document_date' => 'date',
        'exchange_rate' => 'decimal:10',
        'revaluation_rate' => 'decimal:10',
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

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /** @return HasMany<SalesInvoiceLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(SalesInvoiceLine::class)->orderBy('line_no');
    }

    /** @return HasMany<ArAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(ArAllocation::class);
    }

    /** @return HasMany<BadDebtWriteoff, $this> */
    public function writeoffs(): HasMany
    {
        return $this->hasMany(BadDebtWriteoff::class);
    }

    /**
     * Outstanding amount = gross − allocated − write-offs (document currency).
     *
     * @return numeric-string
     */
    public function openBalance(): string
    {
        $writtenOff = '0';
        foreach ($this->writeoffs as $writeoff) {
            $writtenOff = Decimal::add($writtenOff, Decimal::of($writeoff->amount));
        }

        return Decimal::sub(
            Decimal::of($this->gross_total ?? '0'),
            Decimal::add(Decimal::of($this->allocated_total ?? '0'), $writtenOff),
        );
    }

    /** Rate used for functional conversion after optional period-end revaluation. */
    public function effectiveFxRate(): string
    {
        if ($this->revaluation_rate !== null) {
            return Decimal::of((string) $this->revaluation_rate);
        }

        return Decimal::of((string) $this->exchange_rate);
    }
}
