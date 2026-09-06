<?php

declare(strict_types=1);

namespace App\Models\Revenue;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeferredRevenue extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'amount' => 'decimal:6',
        'recognized' => 'decimal:6',
        'periods' => 'integer',
        'periods_recognized' => 'integer',
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
}
