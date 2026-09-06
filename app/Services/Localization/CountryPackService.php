<?php

declare(strict_types=1);

namespace App\Services\Localization;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Localization\LocalizationRule;
use App\Models\Localization\LocalizationSequence;
use App\Models\Localization\StatutoryReturn;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Localization\Exceptions\RuleNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Country pack (Libya first): statutory artefacts from the localization layer,
 * never from hard-coded rates in the accounting core.
 */
class CountryPackService
{
    public function __construct(
        private readonly RuleResolver $rules,
        private readonly TrialBalanceService $trialBalance,
        private readonly AccountRoleResolver $roles,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function publishRule(array $attributes): LocalizationRule
    {
        if (! isset($attributes['legal_reference']) || trim((string) $attributes['legal_reference']) === '') {
            throw new PostingException('A localization rule requires a legal_reference.');
        }

        return LocalizationRule::create([
            'country' => $attributes['country'],
            'rule_code' => $attributes['rule_code'],
            'rule_type' => $attributes['rule_type'],
            'name_ar' => $attributes['name_ar'] ?? $attributes['rule_code'],
            'authority' => $attributes['authority'] ?? null,
            'legal_reference' => $attributes['legal_reference'],
            'version' => (int) ($attributes['version'] ?? 1),
            'status' => $attributes['status'] ?? 'active',
            'rate' => $attributes['rate'] ?? null,
            'amount' => $attributes['amount'] ?? null,
            'effective_from' => $attributes['effective_from'],
            'effective_to' => $attributes['effective_to'] ?? null,
            'meta' => $attributes['meta'] ?? null,
        ]);
    }

    public function nextDocumentNumber(string $country, string $ruleCode, Carbon|string $onDate): string
    {
        $rule = $this->rules->resolve($country, $ruleCode, Carbon::parse($onDate));
        $meta = is_array($rule->meta) ? $rule->meta : [];
        $prefix = is_string($meta['prefix'] ?? null) ? $meta['prefix'] : $ruleCode.'-';

        return DB::transaction(function () use ($country, $ruleCode, $prefix): string {
            $seq = LocalizationSequence::query()->firstOrCreate(
                ['country' => $country, 'rule_code' => $ruleCode],
                ['last_number' => 0],
            );
            $seq->forceFill(['last_number' => $seq->last_number + 1])->save();

            return $prefix.str_pad((string) $seq->last_number, 5, '0', STR_PAD_LEFT);
        });
    }

    /**
     * @return array{output: numeric-string, input: numeric-string, net: numeric-string, legal_reference: string, rate: numeric-string}
     */
    public function vatReturn(Company $company, AccountingBook $book, string $country, Carbon|string $asOf): array
    {
        $date = Carbon::parse($asOf);
        $rule = $this->rules->resolve($country, 'LY_VAT_STANDARD', $date);
        if ($rule->rate === null) {
            throw RuleNotFoundException::noValue($country, 'LY_VAT_STANDARD');
        }
        $rows = $this->trialBalance->build($company, $book, $date)->keyBy('code');
        $output = $this->creditNet($rows->get($this->roles->code($company, 'tax.output_vat')));
        $input = $this->debitNet($rows->get($this->roles->code($company, 'tax.input_vat')));

        $payload = [
            'output' => $output,
            'input' => $input,
            'net' => Decimal::sub($output, $input),
            'legal_reference' => (string) $rule->legal_reference,
            'rate' => Decimal::of((string) $rule->rate),
        ];

        StatutoryReturn::create([
            'company_id' => $company->id,
            'country' => $country,
            'kind' => 'vat',
            'period_key' => $date->format('Y-m'),
            'payload' => $payload,
        ]);

        return $payload;
    }

    /** @return numeric-string */
    private function creditNet(mixed $row): string
    {
        if ($row === null) {
            return Decimal::of('0');
        }

        return Decimal::sub(Decimal::of($row->credit), Decimal::of($row->debit));
    }

    /** @return numeric-string */
    private function debitNet(mixed $row): string
    {
        if ($row === null) {
            return Decimal::of('0');
        }

        return Decimal::sub(Decimal::of($row->debit), Decimal::of($row->credit));
    }
}
