<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Models\Project\Project;
use App\Models\Project\ProjectCost;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class ProjectCostService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly AccountRoleResolver $roles,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $options  source, source_ref, debit_account, credit_account, dimensions
     */
    public function charge(Project $project, string $date, string|float|int $amount, array $options = []): ProjectCost
    {
        if ($project->status !== 'open') {
            throw new PostingException('Cannot charge a closed project.');
        }
        $amt = Decimal::of($amount);
        if (! Decimal::isPositive($amt)) {
            throw new PostingException('Project cost must be positive.');
        }
        $company = $project->company;
        $debit = is_string($options['debit_account'] ?? null) && $options['debit_account'] !== ''
            ? (string) $options['debit_account']
            : $this->roles->code($company, 'project.cost');
        $credit = is_string($options['credit_account'] ?? null) && $options['credit_account'] !== ''
            ? (string) $options['credit_account']
            : $this->roles->code($company, 'bank');
        $this->controls->assertPostingAccount($company, $debit);
        $this->controls->assertPostingAccount($company, $credit);
        $dims = is_array($options['dimensions'] ?? null) ? $options['dimensions'] : (is_array($project->dimensions) ? $project->dimensions : []);

        return DB::transaction(function () use ($project, $date, $amt, $options, $company, $debit, $credit, $dims): ProjectCost {
            $journal = $this->poster->post($company, 'project.cost', $date, (string) ($project->currency ?? $company->functional_currency), $project->code, [
                ['account' => $debit, 'debit' => $amt, 'memo' => 'Project cost '.$project->code, 'dimensions' => $dims],
                ['account' => $credit, 'credit' => $amt, 'memo' => 'Project cost credit', 'dimensions' => $dims],
            ]);
            $cost = $project->costs()->create([
                'company_id' => $project->company_id,
                'type' => 'charge',
                'source' => $options['source'] ?? 'manual',
                'source_ref' => $options['source_ref'] ?? null,
                'cost_date' => $date,
                'amount' => $amt,
                'capitalized_amount' => '0',
                'journal_id' => $journal->id,
                'dimensions' => $dims,
            ]);
            $project->forceFill(['charged' => Decimal::add(Decimal::of($project->charged), $amt)])->save();
            $this->audit->record($project, 'posted', $company->id, null, ['cost_id' => $cost->id, 'journal_id' => $journal->id]);

            return $cost;
        });
    }
}
