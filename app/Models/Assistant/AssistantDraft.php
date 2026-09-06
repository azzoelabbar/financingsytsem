<?php

declare(strict_types=1);

namespace App\Models\Assistant;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property list<array<string, mixed>> $proposed_lines
 */
class AssistantDraft extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'proposed_lines' => 'array',
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
