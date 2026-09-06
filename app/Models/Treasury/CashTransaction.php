<?php

declare(strict_types=1);

namespace App\Models\Treasury;

use App\Enums\Treasury\CashTransactionType;
use App\Enums\Treasury\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => CashTransactionType::class,
        'status' => DocumentStatus::class,
        'transaction_date' => 'date',
        'value_date' => 'date',
        'cleared_date' => 'date',
        'exchange_rate' => 'decimal:10',
        'amount' => 'decimal:6',
        'is_cleared' => 'boolean',
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

    /** @return BelongsTo<TreasuryAccount, $this> */
    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(TreasuryAccount::class);
    }

    /** @return BelongsTo<TreasuryAccount, $this> */
    public function counterTreasuryAccount(): BelongsTo
    {
        return $this->belongsTo(TreasuryAccount::class, 'counter_treasury_account_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
