<?php

declare(strict_types=1);

namespace App\Models\Lease;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lease extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'commencement_date' => 'date',
        'present_value' => 'decimal:6',
        'liability' => 'decimal:6',
        'rou_cost' => 'decimal:6',
        'accum_depreciation' => 'decimal:6',
        'interest_rate' => 'decimal:6',
        'term_months' => 'integer',
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
