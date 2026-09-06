<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Enums\Accounting\PeriodStatus;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\FiscalPeriod;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Gl\Exceptions\PeriodCloseException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PeriodIndex extends Component
{
    use InteractsWithAccountingContext;

    public string $reopenReason = '';

    public function changeStatus(int $periodId, string $target, GlApplicationService $service): void
    {
        $company = $this->requireCompany();
        $period = FiscalPeriod::query()->where('company_id', $company->id)->findOrFail($periodId);
        $status = PeriodStatus::tryFrom($target);
        if ($status === null) {
            $this->addError('period', __('erp.period_page.invalid_transition'));

            return;
        }
        if ($status === PeriodStatus::OPEN) {
            $this->validate(['reopenReason' => 'required|string|max:500']);
        }
        try {
            $actorId = auth()->id();
            $actorId = is_int($actorId) ? $actorId : null;
            $service->transitionPeriod($period, $status, $actorId, $this->reopenReason);
            session()->flash('success', __('erp.period_page.updated'));
        } catch (PostingException|PeriodCloseException $exception) {
            $this->addError('period', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $company = $this->company();
        $periods = $company
            ? app(GlApplicationService::class)->listPeriods($company, $this->listRequest())
            : null;

        return view('livewire.gl.period-index', compact('periods'));
    }
}
