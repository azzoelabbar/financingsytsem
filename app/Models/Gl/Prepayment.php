<?php

declare(strict_types=1);

namespace App\Models\Gl;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prepayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'prepayment_date' => 'date',
        'amount' => 'decimal:6',
        'amount_recognized' => 'decimal:6',
        'periods' => 'integer',
        'periods_recognized' => 'integer',
        'exchange_rate' => 'decimal:10',
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

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return HasMany<PrepaymentSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(PrepaymentSchedule::class)->orderBy('period_no');
    }

    /**
     * @return numeric-string
     */
    public function remaining(): string
    {
        return Decimal::sub(Decimal::of($this->amount ?? '0'), Decimal::of($this->amount_recognized ?? '0'));
    }
}
