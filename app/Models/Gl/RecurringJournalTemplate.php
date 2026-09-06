<?php

declare(strict_types=1);

namespace App\Models\Gl;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringJournalTemplate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'day_of_month' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_run_date' => 'date',
        'exchange_rate' => 'decimal:10',
        'is_active' => 'boolean',
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

    /** @return HasMany<RecurringJournalTemplateLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(RecurringJournalTemplateLine::class)->orderBy('line_no');
    }
}
