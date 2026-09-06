<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Models\Project\Project;
use App\Models\Project\ProjectCapitalization;
use App\Models\Project\ProjectCost;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class ProjectCapitalizationService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly AccountRoleResolver $roles,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    public function capitalize(Project $project, string $date, string|float|int $amount, ?ProjectCost $cost = null): ProjectCapitalization
    {
        if ($project->status !== 'open') {
            throw new PostingException('Cannot capitalize a closed project.');
        }
        $amt = Decimal::of($amount);
        if (! Decimal::isPositive($amt)) {
            throw new PostingException('Capitalization amount must be positive.');
        }
        $cost ??= $project->costs()->orderBy('id')->get()->first(
            fn (ProjectCost $c): bool => Decimal::compare(Decimal::of($c->amount), Decimal::of($c->capitalized_amount)) > 0
        );
        if ($cost === null) {
            throw new PostingException('No uncapitalized project cost remains.');
        }
        $open = Decimal::sub(Decimal::of($cost->amount), Decimal::of($cost->capitalized_amount));
        if (Decimal::compare($amt, $open) > 0) {
            throw new PostingException('Cannot capitalize more than uncapitalized project cost.');
        }
        if (ProjectCapitalization::query()->where('project_cost_id', $cost->id)->where('amount', $amt)->whereDate('capitalized_on', $date)->exists()) {
            throw new PostingException('Duplicate capitalization for this cost.');
        }
        $company = $project->company;
        $cwip = $this->roles->code($company, 'project.cwip');
        $expense = $cost->type === 'charge'
            ? $this->roles->code($company, 'project.cost')
            : $this->roles->code($company, 'project.cost');
        $this->controls->assertPostingAccount($company, $cwip);
        $dims = is_array($cost->dimensions) ? $cost->dimensions : [];

        return DB::transaction(function () use ($project, $cost, $date, $amt, $company, $cwip, $expense, $dims): ProjectCapitalization {
            $journal = $this->poster->post($company, 'project.capitalize', $date, (string) ($project->currency ?? $company->functional_currency), $project->code, [
                ['account' => $cwip, 'debit' => $amt, 'memo' => 'Capitalize to CWIP', 'dimensions' => $dims],
                ['account' => $expense, 'credit' => $amt, 'memo' => 'Remove project expense', 'dimensions' => $dims],
            ]);
            $cap = $project->capitalizations()->create([
                'project_cost_id' => $cost->id,
                'capitalized_on' => $date,
                'amount' => $amt,
                'journal_id' => $journal->id,
            ]);
            $cost->forceFill(['capitalized_amount' => Decimal::add(Decimal::of($cost->capitalized_amount), $amt)])->save();
            $project->forceFill(['capitalized' => Decimal::add(Decimal::of($project->capitalized), $amt)])->save();
            $this->audit->record($project, 'capitalized', $company->id, null, ['capitalization_id' => $cap->id, 'journal_id' => $journal->id]);

            return $cap;
        });
    }
}
