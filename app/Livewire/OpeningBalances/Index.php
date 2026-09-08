<?php

declare(strict_types=1);

namespace App\Livewire\OpeningBalances;

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
        $book = $this->book();
        $batches = ($company && $book)
            ? app(DomainApplicationService::class)->listOpeningBalances($company, $book, $this->listRequest(searchColumns: 'as_of,currency'))
            : null;

        return view('livewire.opening-balances.index', compact('batches'));
    }

    protected function excelTitle(): string
    {
        return __('erp.opening.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $batches = app(DomainApplicationService::class)->listOpeningBalances(
            $company,
            $book,
            $this->exportRequest(searchColumns: 'as_of,currency'),
        );

        return [$this->excelSheetFrom(
            __('erp.opening.title'),
            [
                [__('erp.opening.as_of'), ExcelSheet::DATE, fn ($b) => $this->exportDate($b->as_of)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($b) => $b->currency],
                [__('erp.status'), ExcelSheet::TEXT, fn ($b) => $this->statusLabel($b->status)],
            ],
            $batches,
        )];
    }
}
