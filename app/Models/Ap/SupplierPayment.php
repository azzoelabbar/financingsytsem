<?php

declare(strict_types=1);

namespace App\Models\Ap;

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payment_date' => 'date',
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

    /** @return BelongsTo<Supplier, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return HasMany<ApAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(ApAllocation::class);
    }
}
