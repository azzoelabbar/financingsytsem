<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Services\Accounting\Support\Decimal;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CashFlow extends Component
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
        $report = ($company && $book)
            ? app(DomainApplicationService::class)->cashFlow($company, $book, $this->asOf)
            : [];

        return view('livewire.reports.cash-flow', [
            'report' => $report,
            'movements' => $company ? $this->movements($report, $company) : [],
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }

    /**
     * Roll the raw cash lines up into one readable row per account + activity,
     * so the page shows "this bank account received X and paid Y" instead of
     * a line per journal entry.
     *
     * @param  array<string, mixed>  $report
     * @return list<array{account: string, name: string, classification: string, in: numeric-string, out: numeric-string, net: numeric-string, count: int}>
     */
    private function movements(array $report, Company $company): array
    {
        $lines = is_array($report['lines'] ?? null) ? $report['lines'] : [];

        if ($lines === []) {
            return [];
        }

        $names = Account::query()
            ->where('company_id', $company->id)
            ->pluck('name_ar', 'code');

        $rows = [];
        foreach ($lines as $line) {
            $code = (string) ($line['account'] ?? '');
            $class = (string) ($line['classification'] ?? 'operating');
            $key = $code.'|'.$class;

            $rows[$key] ??= [
                'account' => $code,
                'name' => (string) ($names[$code] ?? $code),
                'classification' => $class,
                'in' => '0',
                'out' => '0',
                'net' => '0',
                'count' => 0,
            ];

            $rows[$key]['in'] = Decimal::add($rows[$key]['in'], Decimal::of($this->numeric($line['debit'] ?? null)));
            $rows[$key]['out'] = Decimal::add($rows[$key]['out'], Decimal::of($this->numeric($line['credit'] ?? null)));
            $rows[$key]['net'] = Decimal::sub($rows[$key]['in'], $rows[$key]['out']);
            $rows[$key]['count']++;
        }

        return array_values($rows);
    }

    /** Coerce a raw ledger amount into something Decimal can safely parse. */
    private function numeric(mixed $value): string
    {
        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '0';
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.cash_flow');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(DomainApplicationService::class)->cashFlow($company, $book, $this->asOf);
        $meta = $this->excelMeta([__('erp.aging.as_of') => $this->asOf]);

        return [
            $this->excelKeyValueSheet(__('erp.export.sheet_summary'), [
                __('erp.reports.activity_operating') => $report['operating'] ?? '0',
                __('erp.reports.activity_investing') => $report['investing'] ?? '0',
                __('erp.reports.activity_financing') => $report['financing'] ?? '0',
                __('erp.reports.cash_in') => $report['inflows'] ?? '0',
                __('erp.reports.cash_out') => $report['outflows'] ?? '0',
                __('erp.reports.net_cash_change') => $report['net'] ?? '0',
            ], meta: $meta),
            $this->excelSheetFrom(
                __('erp.reports.cash_movements'),
                [
                    [__('erp.code'), ExcelSheet::TEXT, fn (array $r) => $r['account']],
                    [__('erp.name'), ExcelSheet::TEXT, fn (array $r) => $r['name']],
                    [__('erp.reports.activity_label'), ExcelSheet::TEXT, fn (array $r) => __('erp.reports.activity_'.$r['classification'])],
                    [__('erp.reports.cash_in'), ExcelSheet::MONEY, fn (array $r) => $r['in']],
                    [__('erp.reports.cash_out'), ExcelSheet::MONEY, fn (array $r) => $r['out']],
                    [__('erp.reports.net_cash_change'), ExcelSheet::MONEY, fn (array $r) => $r['net']],
                ],
                $this->movements($report, $company),
                $meta,
            ),
        ];
    }
}
