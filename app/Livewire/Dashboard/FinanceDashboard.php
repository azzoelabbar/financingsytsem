<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Application\Api\Ap\ApApplicationService;
use App\Application\Api\Ar\ArApplicationService;
use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Journal;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class FinanceDashboard extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $period = $this->period();

        $kpis = [
            'cash' => '0',
            'ar_total' => '0',
            'ap_total' => '0',
            'revenue' => '0',
            'expenses' => '0',
            'net_profit' => '0',
            'total_assets' => '0',
            'total_liabilities' => '0',
            'equity' => '0',
            'tb_balanced' => false,
        ];

        $arAging = null;
        $apAging = null;
        /** @var Collection<int, Journal> $recentJournals */
        $recentJournals = collect();
        $arReconciliation = null;
        $apReconciliation = null;

        if ($company !== null && $book !== null) {
            $arAging = app(ArApplicationService::class)->aging($company, book: $book);
            $apAging = app(ApApplicationService::class)->aging($company, book: $book);
            $kpis['ar_total'] = $arAging['total'] ?? '0';
            $kpis['ap_total'] = $apAging['total'] ?? '0';
        }

        if ($company !== null && $book !== null) {
            $gl = app(GlApplicationService::class);

            $tb = $gl->trialBalance($company, $book);
            $kpis['tb_balanced'] = (bool) ($tb['totals']['balanced'] ?? false);

            $pl = $gl->profitAndLoss($company, $book);
            $kpis['revenue'] = $pl['revenue'] ?? '0';
            $kpis['expenses'] = $pl['expenses'] ?? '0';
            $kpis['net_profit'] = $pl['net_profit'] ?? '0';

            $bs = $gl->balanceSheet($company, $book);
            $kpis['total_assets'] = $bs['totals']['assets'] ?? '0';
            $kpis['total_liabilities'] = $bs['totals']['liabilities'] ?? '0';
            $kpis['equity'] = $bs['totals']['equity'] ?? '0';
            $kpis['cash'] = $this->sumCashFromBalanceSheet($bs);

            $recentJournals = Journal::query()
                ->where('company_id', $company->id)
                ->where('book_id', $book->id)
                ->orderByDesc('journal_date')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            $arReconciliation = app(ArApplicationService::class)->reconcile($company, $book);
            $apReconciliation = app(ApApplicationService::class)->reconcile($company, $book);
        }

        return view('livewire.dashboard.finance-dashboard', [
            'company' => $company,
            'book' => $book,
            'period' => $period,
            'kpis' => $kpis,
            'arAging' => $arAging,
            'apAging' => $apAging,
            'recentJournals' => $recentJournals,
            'arReconciliation' => $arReconciliation,
            'apReconciliation' => $apReconciliation,
        ]);
    }

    /**
     * Sum cash and cash-equivalent accounts (1101xx) from balance sheet lines.
     *
     * @param  array<string, mixed>  $balanceSheet
     */
    private function sumCashFromBalanceSheet(array $balanceSheet): string
    {
        $cash = '0';

        foreach ($balanceSheet['lines'] ?? [] as $line) {
            if ($line->group !== 'asset') {
                continue;
            }

            if (str_starts_with($line->code, '1101')) {
                $cash = Decimal::add($cash, $line->amount);
            }
        }

        return $cash;
    }
}
