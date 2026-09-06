<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ProjectCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $code = '';

    public string $name = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $budget = '0';

    public string $currency = 'LYD';

    public function mount(): void
    {
        $this->start_date = now()->toDateString();
        $company = $this->company();
        $this->currency = $company instanceof Company ? (string) $company->functional_currency : 'LYD';
    }

    public function save(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $this->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('projects', 'code')->where('company_id', $company->id)],
            'name' => 'required|string|max:255', 'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date', 'budget' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
        ]);
        try {
            $project = $service->defineProject($company, $this->only(['code', 'name', 'start_date', 'end_date', 'budget', 'currency']));
        } catch (PostingException $exception) {
            $this->addError('form', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.project.created'));
        $this->redirectRoute('projects.show', $project->id, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.projects.project-create');
    }
}
