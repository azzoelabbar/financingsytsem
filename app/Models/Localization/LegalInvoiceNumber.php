<?php

declare(strict_types=1);

namespace App\Models\Localization;

use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalInvoiceNumber extends Model
{
    protected $guarded = ['id'];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
