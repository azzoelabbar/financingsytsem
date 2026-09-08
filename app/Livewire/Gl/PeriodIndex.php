<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Enums\Accounting\PeriodStatus;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\FiscalPeriod;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Gl\Exceptions\PeriodCloseException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PeriodIndex extends Component
{
    use ExportsToExcel;
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
            ? app(GlApplicationService::class)->listPeriods($company, $this->listRequest(searchColumns: 'period_no'))
            : null;

        return view('livewire.gl.period-index', compact('periods'));
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.periods');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $periods = app(GlApplicationService::class)->listPeriods(
            $company,
            $this->exportRequest(searchColumns: 'period_no'),
        );

        return [$this->excelSheetFrom(
            __('erp.nav.periods'),
            [
                [__('erp.period'), ExcelSheet::NUMBER, fn ($p) => $p->period_no],
                [__('erp.period_page.start_date'), ExcelSheet::DATE, fn ($p) => $this->exportDate($p->start_date)],
                [__('erp.period_page.end_date'), ExcelSheet::DATE, fn ($p) => $this->exportDate($p->end_date)],
                [__('erp.status'), ExcelSheet::TEXT, fn ($p) => $this->statusLabel($p->status)],
                [__('erp.period_page.posting'), ExcelSheet::TEXT, fn ($p) => $p->status->value === 'open'
                    ? __('erp.period_page.posting_allowed')
                    : __('erp.period_page.posting_blocked')],
            ],
            $periods,
        )];
    }
}
