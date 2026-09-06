<?php

declare(strict_types=1);

namespace App\Models\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receipt extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'receipt_date' => 'date',
        'exchange_rate' => 'decimal:10',
        'amount' => 'decimal:6',
        'unallocated_amount' => 'decimal:6',
        'status' => DocumentStatus::class,
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

    /** @return HasMany<ArAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(ArAllocation::class);
    }
}
