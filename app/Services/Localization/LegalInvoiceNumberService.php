<?php

declare(strict_types=1);

namespace App\Services\Localization;

use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Localization\LegalInvoiceNumber;
use App\Models\Localization\LocalizationSequence;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalInvoiceNumberService
{
    public function __construct(
        private readonly RuleResolver $rules,
        private readonly AuditLogger $audit,
    ) {}

    public function issue(
        Company $company,
        string $country,
        string $documentType,
        Carbon|string $onDate,
        ?int $sourceId = null,
        ?string $sourceType = null,
    ): LegalInvoiceNumber {
        $date = Carbon::parse($onDate);
        $rule = $this->rules->resolve($country, $documentType, $date);
        $meta = is_array($rule->meta) ? $rule->meta : [];
        $prefix = is_string($meta['prefix'] ?? null) ? $meta['prefix'] : $documentType.'-';
        $period = FiscalPeriod::query()
            ->where('company_id', $company->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
        $periodKey = $period !== null ? $period->start_date->format('Y-m') : $date->format('Y-m');

        return DB::transaction(function () use ($company, $country, $documentType, $prefix, $periodKey, $rule, $sourceId, $sourceType): LegalInvoiceNumber {
            $seq = LocalizationSequence::query()->firstOrCreate(
                ['country' => $country, 'rule_code' => $documentType.'#'.$company->id.'#'.$periodKey],
                ['last_number' => 0],
            );
            $seq->forceFill(['last_number' => $seq->last_number + 1])->save();
            $number = $prefix.str_pad((string) $seq->last_number, 5, '0', STR_PAD_LEFT);
            if (LegalInvoiceNumber::query()->where('company_id', $company->id)->where('document_type', $documentType)->where('number', $number)->exists()) {
                throw new PostingException("Duplicate legal reference '{$number}'.");
            }
            $issued = LegalInvoiceNumber::create([
                'company_id' => $company->id,
                'country' => $country,
                'document_type' => $documentType,
                'period_key' => $periodKey,
                'number' => $number,
                'legal_reference' => $rule->legal_reference,
                'source_id' => $sourceId,
                'source_type' => $sourceType,
            ]);
            $this->audit->record($issued, 'created', $company->id, null, ['number' => $number]);

            return $issued;
        });
    }
}
