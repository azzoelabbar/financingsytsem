<?php

declare(strict_types=1);

namespace App\Services\Fx;

use App\Enums\Accounting\BookBasis;
use App\Enums\Accounting\RateType;
use App\Enums\Ap\DocumentStatus as ApStatus;
use App\Enums\Ar\DocumentStatus as ArStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ar\SalesInvoice;
use App\Models\Fx\FxRevaluationRun;
use App\Models\User;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Period-end revaluation of open foreign-currency AR/AP (IAS 21 unrealized FX).
 */
class FxRevaluationService
{
    public function __construct(
        private readonly ExchangeRateService $rates,
        private readonly AccountingEngine $engine,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    public function revalueOpenItems(
        Company $company,
        AccountingBook $book,
        Carbon|string $asOf,
        ?User $poster = null,
    ): FxRevaluationRun {
        $asOfDate = Carbon::parse($asOf);
        $functional = strtoupper((string) $company->functional_currency);

        return DB::transaction(function () use ($company, $book, $asOfDate, $functional, $poster): FxRevaluationRun {
            $run = FxRevaluationRun::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'as_of_date' => $asOfDate->toDateString(),
                'status' => 'posted',
                'created_by' => $poster?->id,
                'posted_at' => now(),
            ]);

            $totalGain = '0';
            $totalLoss = '0';
            $arControl = $this->controls->arControlCode($company);
            $apControl = $this->controls->apControlCode($company);

            $arInvoices = SalesInvoice::query()
                ->where('company_id', $company->id)
                ->where('status', ArStatus::POSTED)
                ->where('currency', '!=', $functional)
                ->get()
                ->filter(fn (SalesInvoice $i): bool => Decimal::isPositive($i->openBalance()));

            foreach ($arInvoices as $invoice) {
                $result = $this->revalueDocument(
                    $company,
                    $book,
                    $run,
                    'ar',
                    SalesInvoice::class,
                    $invoice->id,
                    $invoice->currency,
                    $invoice->openBalance(),
                    $this->effectiveRate($invoice),
                    $arControl,
                    'asset',
                    $asOfDate,
                    $functional,
                    $poster,
                );
                $totalGain = Decimal::add($totalGain, $result['gain']);
                $totalLoss = Decimal::add($totalLoss, $result['loss']);
                if ($result['closing_rate'] !== null) {
                    $invoice->forceFill(['revaluation_rate' => $result['closing_rate']])->save();
                }
            }

            $apInvoices = PurchaseInvoice::query()
                ->where('company_id', $company->id)
                ->where('status', ApStatus::POSTED)
                ->where('currency', '!=', $functional)
                ->get()
                ->filter(fn (PurchaseInvoice $i): bool => Decimal::isPositive($i->openBalance()));

            foreach ($apInvoices as $invoice) {
                $result = $this->revalueDocument(
                    $company,
                    $book,
                    $run,
                    'ap',
                    PurchaseInvoice::class,
                    $invoice->id,
                    $invoice->currency,
                    $invoice->openBalance(),
                    $this->effectiveRate($invoice),
                    $apControl,
                    'liability',
                    $asOfDate,
                    $functional,
                    $poster,
                );
                $totalGain = Decimal::add($totalGain, $result['gain']);
                $totalLoss = Decimal::add($totalLoss, $result['loss']);
                if ($result['closing_rate'] !== null) {
                    $invoice->forceFill(['revaluation_rate' => $result['closing_rate']])->save();
                }
            }

            $run->forceFill([
                'total_gain' => $totalGain,
                'total_loss' => $totalLoss,
            ])->save();

            $this->audit->record($run, 'revalued', $company->id, null, [
                'gain' => $totalGain,
                'loss' => $totalLoss,
                'lines' => $run->lines()->count(),
            ]);

            return $run->load('lines');
        });
    }

    /**
     * @return array{gain: numeric-string, loss: numeric-string, closing_rate: numeric-string|null}
     */
    private function revalueDocument(
        Company $company,
        AccountingBook $book,
        FxRevaluationRun $run,
        string $side,
        string $documentType,
        int $documentId,
        string $currency,
        string $fcAmount,
        string $historicalRate,
        string $monetaryAccount,
        string $accountSide,
        Carbon $asOf,
        string $functional,
        ?User $poster,
    ): array {
        $closingRate = $this->rates->resolve($company, $currency, $functional, $asOf, RateType::CLOSING);
        $fc = Decimal::of($fcAmount);
        $hist = Decimal::of($historicalRate);
        $close = Decimal::of($closingRate);
        $historicalFunctional = Decimal::mul($fc, $hist);
        $closingFunctional = Decimal::mul($fc, $close);
        $difference = Decimal::sub($closingFunctional, $historicalFunctional);

        $run->lines()->create([
            'side' => $side,
            'document_type' => $documentType,
            'document_id' => $documentId,
            'currency' => $currency,
            'fc_amount' => $fcAmount,
            'historical_rate' => $historicalRate,
            'closing_rate' => $closingRate,
            'historical_functional' => $historicalFunctional,
            'closing_functional' => $closingFunctional,
            'difference' => $difference,
        ]);

        if (Decimal::equals($difference, '0')) {
            return ['gain' => '0', 'loss' => '0', 'closing_rate' => $closingRate];
        }

        // Asset: higher closing → gain. Liability: higher closing → loss.
        $isGain = $accountSide === 'asset'
            ? Decimal::isPositive($difference)
            : Decimal::isNegative($difference);

        $abs = Decimal::isNegative($difference) ? Decimal::sub('0', $difference) : $difference;
        $direction = $isGain ? 'gain' : 'loss';

        // Liability gain posts Dr monetary (reduce liability) — same as asset gain pattern.
        // Liability loss posts Cr monetary — same as asset loss pattern.
        // Already encoded in FxRevaluationRule once direction is P&L-signed.
        $document = new GenericSourceDocument(
            'gl.fx_revaluation',
            $asOf->toDateString(),
            $functional,
            'FXR-'.$run->id.'-'.$side.'-'.$documentId,
            [
                'monetary_account' => $monetaryAccount,
                'amount' => $abs,
                'direction' => $direction,
                'gain_account' => '420104',
                'loss_account' => '630105',
                'book_basis' => BookBasis::LOCAL->value,
                'exchange_rate' => 1,
            ],
        );

        $this->engine->postFrom($company, $document, $poster);

        return [
            'gain' => $isGain ? $abs : '0',
            'loss' => $isGain ? '0' : $abs,
            'closing_rate' => $closingRate,
        ];
    }

    private function effectiveRate(SalesInvoice|PurchaseInvoice $invoice): string
    {
        if ($invoice->revaluation_rate !== null) {
            return Decimal::of((string) $invoice->revaluation_rate);
        }

        return Decimal::of((string) $invoice->exchange_rate);
    }
}
