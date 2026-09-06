<?php

declare(strict_types=1);

namespace App\Models\Fx;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FxTranslationRun extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'as_of_date' => 'date',
        'closing_rate' => 'decimal:10',
        'net_assets_functional' => 'decimal:6',
        'translated_amount' => 'decimal:6',
        'difference' => 'decimal:6',
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
}
