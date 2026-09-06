<?php

declare(strict_types=1);

namespace App\Models\Project;

use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCapitalization extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'capitalized_on' => 'date',
        'amount' => 'decimal:6',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<ProjectCost, $this> */
    public function cost(): BelongsTo
    {
        return $this->belongsTo(ProjectCost::class, 'project_cost_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
