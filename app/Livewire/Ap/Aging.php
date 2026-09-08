<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Aging extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public string $asOf = '';

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $report = $company && $book
            ? app(ApApplicationService::class)->aging($company, Carbon::parse($this->asOf), book: $book)
            : ['buckets' => [], 'total' => '0'];

        return view('livewire.ap.aging', [
            'report' => $report,
            'bucketKeys' => ['current', '1_30', '31_60', '61_90', '91_120', '120_plus', 'credits'],
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.ap_aging');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(ApApplicationService::class)->aging($company, Carbon::parse($this->asOf), book: $book);
        $buckets = $report['buckets'] ?? [];
        $values = [];

        foreach (['current', '1_30', '31_60', '61_90', '91_120', '120_plus', 'credits'] as $key) {
            $values[__('erp.aging.'.$key)] = $buckets[$key] ?? '0';
        }

        $values[__('erp.total')] = $report['total'] ?? '0';

        return [$this->excelKeyValueSheet(
            __('erp.nav.ap_aging'),
            $values,
            meta: $this->excelMeta([__('erp.aging.as_of') => $this->asOf]),
        )];
    }
}
