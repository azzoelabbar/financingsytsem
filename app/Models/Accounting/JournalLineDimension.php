<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLineDimension extends Model
{
    protected $guarded = ['id'];

    /** @return BelongsTo<JournalLine, $this> */
    public function line(): BelongsTo
    {
        return $this->belongsTo(JournalLine::class, 'journal_line_id');
    }

    /** @return BelongsTo<Dimension, $this> */
    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }

    /** @return BelongsTo<DimensionValue, $this> */
    public function value(): BelongsTo
    {
        return $this->belongsTo(DimensionValue::class, 'dimension_value_id');
    }
}
