<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Models\Accounting\Company;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\DimensionValue;
use App\Models\Project\Project;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;

class ProjectService
{
    public function __construct(
        private readonly ProjectCostService $costs,
        private readonly ProjectCapitalizationService $capitalizations,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function define(Company $company, array $data): Project
    {
        $code = is_string($data['code'] ?? $data['project_number'] ?? null)
            ? (string) ($data['code'] ?? $data['project_number'])
            : throw new PostingException('Project number is required.');
        $project = Project::create([
            'company_id' => $company->id,
            'code' => $code,
            'project_number' => $code,
            'name' => $data['name'] ?? $code,
            'status' => 'open',
            'charged' => '0',
            'capitalized' => '0',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => Decimal::of(is_scalar($data['budget'] ?? null) ? (string) $data['budget'] : '0'),
            'currency' => $data['currency'] ?? $company->functional_currency,
            'dimensions' => is_array($data['dimensions'] ?? null) ? $data['dimensions'] : ['PROJECT' => $code],
        ]);
        $dim = Dimension::query()->where('company_id', $company->id)->where('code', 'PROJECT')->first();
        if ($dim !== null) {
            DimensionValue::query()->firstOrCreate(
                ['dimension_id' => $dim->id, 'code' => $code],
                ['name_ar' => $project->name],
            );
        }
        $this->audit->record($project, 'created', $company->id, null, ['code' => $code]);

        return $project;
    }

    public function charge(Project $project, string $date, string|float|int $amount, string $expense = '', string $bank = ''): Project
    {
        $this->costs->charge($project, $date, $amount, [
            'credit_account' => $bank !== '' ? $bank : null,
            'debit_account' => $expense !== '' ? $expense : null,
            'source' => 'manual',
        ]);

        return $project->fresh() ?? $project;
    }

    public function capitalize(Project $project, string $date, string|float|int $amount): Project
    {
        $this->capitalizations->capitalize($project, $date, $amount);

        return $project->fresh() ?? $project;
    }

    public function addMilestone(Project $project, string $name, ?string $dueOn = null): Project
    {
        $project->milestones()->create(['name' => $name, 'due_on' => $dueOn, 'status' => 'open']);

        return $project->fresh(['milestones']) ?? $project;
    }
}
