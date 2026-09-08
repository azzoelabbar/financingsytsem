<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Index extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $projects = $company
            ? app(DomainApplicationService::class)->listProjects($company, $this->listRequest(searchColumns: 'code,name'))
            : null;

        return view('livewire.projects.index', compact('projects'));
    }

    protected function excelTitle(): string
    {
        return __('erp.project.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $projects = app(DomainApplicationService::class)->listProjects(
            $company,
            $this->exportRequest(searchColumns: 'code,name'),
        );

        return [$this->excelSheetFrom(
            __('erp.project.title'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($p) => $p->code],
                [__('erp.project.name'), ExcelSheet::TEXT, fn ($p) => $p->name],
                [__('erp.project.budget'), ExcelSheet::MONEY, fn ($p) => $p->budget],
                [__('erp.project.charged'), ExcelSheet::MONEY, fn ($p) => $p->charged],
                [__('erp.project.capitalized'), ExcelSheet::MONEY, fn ($p) => $p->capitalized],
                [__('erp.status'), ExcelSheet::TEXT, fn ($p) => $this->statusLabel($p->status)],
            ],
            $projects,
        )];
    }
}
