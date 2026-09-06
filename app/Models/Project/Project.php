<?php

declare(strict_types=1);

namespace App\Models\Project;

use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'charged' => 'decimal:6',
        'capitalized' => 'decimal:6',
        'budget' => 'decimal:6',
        'start_date' => 'date',
        'end_date' => 'date',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<ProjectCost, $this> */
    public function costs(): HasMany
    {
        return $this->hasMany(ProjectCost::class);
    }

    /** @return HasMany<ProjectMilestone, $this> */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    /** @return HasMany<ProjectCapitalization, $this> */
    public function capitalizations(): HasMany
    {
        return $this->hasMany(ProjectCapitalization::class);
    }
}
