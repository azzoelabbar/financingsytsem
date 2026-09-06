<?php

declare(strict_types=1);

namespace App\Services\Risk;

use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\Risk\RiskFlag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RiskService
{
    /**
     * @return list<RiskFlag>
     */
    public function scan(Company $company): array
    {
        $flags = [];
        $flags = array_merge($flags, $this->duplicateInvoices($company));
        $flags = array_merge($flags, $this->backdatedPostings($company));

        return $flags;
    }

    /** @return list<RiskFlag> */
    private function duplicateInvoices(Company $company): array
    {
        $dupes = DB::table('sales_invoices')
            ->where('company_id', $company->id)
            ->selectRaw('customer_id, invoice_date, gross_total, COUNT(*) as c')
            ->groupBy('customer_id', 'invoice_date', 'gross_total')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $out = [];
        foreach ($dupes as $row) {
            $out[] = RiskFlag::create([
                'company_id' => $company->id,
                'code' => 'DUPLICATE_INVOICE',
                'severity' => 'high',
                'score' => 80,
                'details' => ['count' => (int) $row->c],
            ]);
        }

        return $out;
    }

    /** @return list<RiskFlag> */
    private function backdatedPostings(Company $company): array
    {
        $cutoff = Carbon::now()->subDays(90)->toDateString();
        $count = Journal::query()
            ->where('company_id', $company->id)
            ->whereDate('journal_date', '<', $cutoff)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        if ($count === 0) {
            return [];
        }

        return [RiskFlag::create([
            'company_id' => $company->id,
            'code' => 'BACKDATED_POSTING',
            'severity' => 'medium',
            'score' => 40,
            'details' => ['count' => $count],
        ])];
    }
}
