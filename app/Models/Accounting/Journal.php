<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\JournalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'journal_date' => 'date',
        'posting_date' => 'date',
        'document_date' => 'date',
        'exchange_rate' => 'decimal:10',
        'status' => JournalStatus::class,
        'total_debit' => 'decimal:6',
        'total_credit' => 'decimal:6',
        'reversed_at' => 'datetime',
        'posted_at' => 'datetime',
        'is_system_generated' => 'boolean',
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

    /** @return BelongsTo<FiscalPeriod, $this> */
    public function period(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class, 'fiscal_period_id');
    }

    /** @return HasMany<JournalLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('line_no');
    }

    /** @return BelongsTo<Journal, $this> */
    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'reversal_of_journal_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'reversed_by_journal_id');
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

    public function isBalanced(): bool
    {
        return bccomp((string) $this->total_debit, (string) $this->total_credit, 6) === 0;
    }
}
