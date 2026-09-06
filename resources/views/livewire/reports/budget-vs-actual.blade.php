<div>
    <x-ui.report-shell
        :title="__('erp.reports.budget_vs_actual')"
        :description="__('erp.reports.bva_hint')"
        :breadcrumbs="[['label' => __('erp.nav.reports')], ['label' => __('erp.nav.budget_vs_actual')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter model="asOf" />
        </x-slot:filters>

        <form wire:submit="saveBudget" class="mb-6">
            <x-ui.card :title="__('erp.reports.budget_entry_title')" :description="__('erp.reports.budget_entry_hint')">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <x-ui.field :label="__('erp.reports.period_key')" for="budget-period" required :error="$errors->first('budgetPeriod')">
                        <input id="budget-period" type="month" wire:model="budgetPeriod" class="erp-control" dir="ltr" />
                    </x-ui.field>
                    <x-ui.searchable-select :label="__('erp.account')" required :error="$errors->first('budgetAccount')" id="budget-account" wire:model="budgetAccount">
                            <option value="">{{ __('erp.select') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale() === 'ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>
                            @endforeach
                        </x-ui.searchable-select>
                    <x-ui.field :label="__('erp.project.budget')" for="budget-amount" required :error="$errors->first('budgetAmount')">
                        <input id="budget-amount" type="number" min="0.000001" step="0.000001" wire:model="budgetAmount" class="erp-control text-end tabular-nums" dir="ltr" />
                    </x-ui.field>
                    <div class="flex items-end">
                        <x-ui.button type="submit" class="w-full">{{ __('erp.reports.save_budget') }}</x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </form>

    @if ($report === [])
        <x-ui.empty-state :title="__('erp.reports.bva_empty_title')" :message="__('erp.reports.bva_empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.reports.period_key') }}</th>
                <th>{{ __('erp.code') }}</th>
                <th class="!text-end">{{ __('erp.project.budget') }}</th>
                <th class="!text-end">{{ __('erp.reports.actual') }}</th>
                <th class="!text-end">{{ __('erp.reports.variance') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($report as $row)
                    <tr wire:key="bva-{{ $row['period_key'] }}-{{ $row['account_code'] }}">
                        <td class="text-muted-foreground" dir="ltr">{{ $row['period_key'] }}</td>
                        <td class="font-mono text-xs" dir="ltr">{{ $row['account_code'] }}</td>
                        <td class="text-end"><x-ui.money :amount="$row['budget']" /></td>
                        <td class="text-end"><x-ui.money :amount="$row['actual']" /></td>
                        <td class="text-end"><x-ui.money :amount="$row['variance']" :negative="(float) $row['variance'] < 0" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif
</x-ui.report-shell>
</div>
