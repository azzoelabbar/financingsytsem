<?php

declare(strict_types=1);

namespace App\Models\Project;

use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCost extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'cost_date' => 'date',
        'amount' => 'decimal:6',
        'capitalized_amount' => 'decimal:6',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return HasMany<ProjectCapitalization, $this> */
    public function capitalizations(): HasMany
    {
        return $this->hasMany(ProjectCapitalization::class);
    }
}
