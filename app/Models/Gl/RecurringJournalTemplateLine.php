<?php

declare(strict_types=1);

namespace App\Models\Gl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringJournalTemplateLine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'line_no' => 'integer',
        'debit' => 'decimal:6',
        'credit' => 'decimal:6',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<RecurringJournalTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(RecurringJournalTemplate::class, 'recurring_journal_template_id');
    }
}
