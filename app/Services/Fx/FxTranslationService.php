<?php

declare(strict_types=1);

namespace App\Services\Fx;

use App\Enums\Accounting\BookBasis;
use App\Enums\Accounting\RateType;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Fx\FxTranslationRun;
use App\Models\User;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Fx\Exceptions\FxException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Presentation-currency translation residual → OCI CTA (330102).
 * Uses net assets (equity incl. result) × closing rate change vs prior CTA basis.
 */
class FxTranslationService
{
    public function __construct(
        private readonly ExchangeRateService $rates,
        private readonly AccountingEngine $engine,
        private readonly FinancialStatementService $statements,
        private readonly AuditLogger $audit,
    ) {}

    public function translate(
        Company $company,
        AccountingBook $book,
        Carbon|string $asOf,
        ?string $priorRate = null,
        ?User $poster = null,
    ): FxTranslationRun {
        $asOfDate = Carbon::parse($asOf);
        $from = strtoupper((string) $company->functional_currency);
        $to = strtoupper((string) $company->presentation_currency);

        if ($from === $to) {
            throw new FxException('Presentation currency equals functional currency; no translation required.');
        }

        return DB::transaction(function () use ($company, $book, $asOfDate, $from, $to, $priorRate, $poster): FxTranslationRun {
            $closingRate = $this->rates->resolve($company, $from, $to, $asOfDate, RateType::CLOSING);
            $bs = $this->statements->balanceSheet(new ReportRequest(
                companyId: $company->id,
                bookId: $book->id,
                asOf: $asOfDate->toDateString(),
            ));
            $netAssets = Decimal::sub($bs['totals']['assets'], $bs['totals']['liabilities']);

            $translated = Decimal::mul($netAssets, $closingRate);
            $basisRate = $priorRate !== null ? Decimal::of($priorRate) : Decimal::of('1');
            $priorTranslated = Decimal::mul($netAssets, $basisRate);
            $difference = Decimal::sub($translated, $priorTranslated);

            $journalId = null;
            if (! Decimal::equals($difference, '0')) {
                $abs = Decimal::isNegative($difference) ? Decimal::sub('0', $difference) : $difference;
                $direction = Decimal::isPositive($difference) ? 'credit_oci' : 'debit_oci';

                $document = new GenericSourceDocument(
                    'gl.fx_translation',
                    $asOfDate->toDateString(),
                    $from,
                    'FXT-'.$asOfDate->format('Ymd'),
                    [
                        'amount' => $abs,
                        'direction' => $direction,
                        'oci_account' => '330102',
                        'contra_account' => '320202',
                        'book_basis' => BookBasis::LOCAL->value,
                        'exchange_rate' => 1,
                    ],
                );
                $journal = $this->engine->postFrom($company, $document, $poster);
                $journalId = $journal->id;
            }

            $run = FxTranslationRun::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'as_of_date' => $asOfDate->toDateString(),
                'from_currency' => $from,
                'to_currency' => $to,
                'closing_rate' => $closingRate,
                'net_assets_functional' => $netAssets,
                'translated_amount' => $translated,
                'difference' => $difference,
                'journal_id' => $journalId,
                'created_by' => $poster?->id,
                'posted_at' => now(),
            ]);

            $this->audit->record($run, 'translated', $company->id, null, [
                'difference' => $difference,
                'closing_rate' => $closingRate,
            ]);

            return $run;
        });
    }
}
