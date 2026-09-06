<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CashFlow extends Component
{
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
}
