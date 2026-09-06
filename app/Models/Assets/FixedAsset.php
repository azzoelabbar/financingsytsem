<?php

declare(strict_types=1);

namespace App\Models\Assets;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAsset extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'cost' => 'decimal:6',
        'accum_depreciation' => 'decimal:6',
        'accum_impairment' => 'decimal:6',
        'useful_life_months' => 'integer',
        'in_service_date' => 'date',
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

    /** @return numeric-string */
    public function netBookValue(): string
    {
        if ($this->status === 'disposed') {
            return '0.000000';
        }

        return Decimal::sub(
            Decimal::sub(Decimal::of($this->cost), Decimal::of($this->accum_depreciation)),
            Decimal::of($this->accum_impairment),
        );
    }
}
