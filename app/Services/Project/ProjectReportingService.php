<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Models\Project\Project;
use App\Services\Accounting\Support\Decimal;

class ProjectReportingService
{
    /**
     * @return array{charged: numeric-string, capitalized: numeric-string, uncapitalized: numeric-string, budget: numeric-string, actual: numeric-string, variance: numeric-string, costs: list<array{id: int, source: string, source_ref: ?string, amount: string, journal_id: ?int}>}
     */
    public function summary(Project $project): array
    {
        $charged = Decimal::of($project->charged);
        $capitalized = Decimal::of($project->capitalized);
        $budget = Decimal::of($project->budget ?? '0');
        $costs = [];
        foreach ($project->costs()->orderBy('id')->get() as $cost) {
            $costs[] = [
                'id' => $cost->id,
                'source' => (string) $cost->source,
                'source_ref' => $cost->source_ref,
                'amount' => Decimal::of($cost->amount),
                'journal_id' => $cost->journal_id,
            ];
        }

        return [
            'charged' => $charged,
            'capitalized' => $capitalized,
            'uncapitalized' => Decimal::sub($charged, $capitalized),
            'budget' => $budget,
            'actual' => $charged,
            'variance' => Decimal::sub($budget, $charged),
            'costs' => $costs,
        ];
    }
}
