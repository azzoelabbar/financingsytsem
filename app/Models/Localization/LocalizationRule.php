<?php

declare(strict_types=1);

namespace App\Models\Localization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single versioned, effective-dated legal/tax rule for one country. Lives in
 * the Localization layer, entirely outside the accounting core.
 */
class LocalizationRule extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rate' => 'decimal:10',
        'amount' => 'decimal:6',
        'meta' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'version' => 'integer',
        'approved_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isActiveOn(\DateTimeInterface $date): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->effective_from && $date < $this->effective_from->startOfDay()) {
            return false;
        }

        if ($this->effective_to && $date > $this->effective_to->endOfDay()) {
            return false;
        }

        return true;
    }
}
