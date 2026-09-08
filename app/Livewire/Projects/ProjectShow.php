<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Project\Project;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ProjectShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public Project $project;

    public string $actionDate = '';

    public string $costAmount = '';

    public string $capitalizationAmount = '';

    public function mount(Project $project): void
    {
        $company = $this->company();
        if ($company === null || $project->company_id !== $company->id) {
            abort(404);
        }
        $this->project = $project;
        $this->actionDate = now()->toDateString();
    }

    public function charge(DomainApplicationService $service): void
    {
        $this->validate(['actionDate' => 'required|date', 'costAmount' => 'required|numeric|gt:0']);
        try {
            $service->chargeProject($this->project, $this->actionDate, $this->costAmount);
            $this->project->refresh();
            $this->costAmount = '';
            session()->flash('success', __('erp.project.cost_posted'));
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function capitalize(DomainApplicationService $service): void
    {
        $this->validate(['actionDate' => 'required|date', 'capitalizationAmount' => 'required|numeric|gt:0']);
        try {
            $service->capitalizeProject($this->project, $this->actionDate, $this->capitalizationAmount);
            $this->project->refresh();
            $this->capitalizationAmount = '';
            session()->flash('success', __('erp.project.capitalized_success'));
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $this->project->loadMissing(['costs.journal', 'capitalizations']);

        $budget = (string) ($this->project->budget ?? '0');
        $charged = (string) ($this->project->charged ?? '0');
        $remaining = Decimal::sub($budget, $charged);
        $variancePct = Decimal::compare($budget, '0') === 0
            ? null
            : (float) $charged / (float) $budget * 100;

        return view('livewire.projects.project-show', [
            'project' => $this->project,
            'budget' => $budget,
            'charged' => $charged,
            'remaining' => $remaining,
            'capitalized' => (string) ($this->project->capitalized ?? '0'),
            'variancePct' => $variancePct,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.project.title').' '.$this->project->code;
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->project->loadMissing(['costs', 'capitalizations']);

        $budget = (string) ($this->project->budget ?? '0');
        $charged = (string) ($this->project->charged ?? '0');

        $meta = $this->excelMeta([
            __('erp.code') => $this->project->code,
            __('erp.project.name') => $this->project->name,
            __('erp.project.budget') => $budget,
            __('erp.project.charged') => $charged,
            __('erp.project.remaining') => Decimal::sub($budget, $charged),
            __('erp.project.capitalized') => (string) ($this->project->capitalized ?? '0'),
            __('erp.status') => $this->statusLabel($this->project->status),
        ]);

        return [
            $this->excelSheetFrom(
                __('erp.project.costs'),
                [
                    [__('erp.date'), ExcelSheet::DATE, fn ($c) => $this->exportDate($c->cost_date)],
                    [__('erp.project.cost_type'), ExcelSheet::TEXT, fn ($c) => __('erp.project.cost_types.'.($c->type ?? ''))],
                    [__('erp.amount'), ExcelSheet::MONEY, fn ($c) => $c->amount],
                    [__('erp.document.journal'), ExcelSheet::TEXT, fn ($c) => $c->journal_id],
                ],
                $this->project->costs,
                $meta,
                heading: __('erp.project.title').' '.$this->project->code,
            ),
            $this->excelSheetFrom(
                __('erp.project.capitalization'),
                [
                    [__('erp.date'), ExcelSheet::DATE, fn ($c) => $this->exportDate($c->capitalized_on)],
                    [__('erp.amount'), ExcelSheet::MONEY, fn ($c) => $c->amount],
                    [__('erp.document.journal'), ExcelSheet::TEXT, fn ($c) => $c->journal_id],
                ],
                $this->project->capitalizations,
                $meta,
            ),
        ];
    }
}
