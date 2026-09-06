<div>
    @php
        $rows = $report['rows'] ?? [];
        $totals = $report['totals'] ?? [];
        $balanced = (bool) ($totals['balanced'] ?? false);
        $cur = $company?->functional_currency ?? '';
    @endphp

    <x-ui.report-shell
        :title="__('erp.nav.trial_balance')"
        :description="__('erp.trial_balance.hint')"
        :breadcrumbs="[['label' => __('erp.nav.gl')], ['label' => __('erp.nav.trial_balance')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter :label="__('erp.aging.as_of')" model="asOf" />
        </x-slot:filters>

        @if ($rows === [])
            <x-ui.empty-state :title="__('erp.trial_balance.empty_title')" :message="__('erp.trial_balance.empty_hint')" />
        @else
            {{-- The equality of the two columns is the point of this report, so it is stated first. --}}
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border px-5 py-3.5"
                 style="border-color: {{ $balanced ? 'var(--success-line)' : 'var(--danger-line)' }}; background: {{ $balanced ? 'var(--success-muted)' : 'var(--danger-muted)' }}">
                <div class="flex items-center gap-2.5">
                    <span class="text-[0.8125rem] font-semibold text-foreground">{{ $balanced ? __('erp.trial_balance.balanced_title') : __('erp.trial_balance.unbalanced_title') }}</span>
                    <x-ui.badge :variant="$balanced ? 'success' : 'danger'">{{ $balanced ? __('erp.balanced') : __('erp.unbalanced') }}</x-ui.badge>
                </div>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-[0.8125rem]">
                    <span class="flex items-center gap-2">
                        <span class="text-muted-foreground">{{ __('erp.debit') }}</span>
                        <x-ui.money :amount="$totals['debit'] ?? '0'" :currency="$cur" size="lg" />
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="text-muted-foreground">{{ __('erp.credit') }}</span>
                        <x-ui.money :amount="$totals['credit'] ?? '0'" :currency="$cur" size="lg" />
                    </span>
                </div>
            </div>

            <x-ui.table density="compact">
                <thead>
                    <tr>
                        <th class="w-28">{{ __('erp.code') }}</th>
                        <th>{{ __('erp.name') }}</th>
                        <th class="!text-end">{{ __('erp.debit') }}</th>
                        <th class="!text-end">{{ __('erp.credit') }}</th>
                        <th class="!text-end">{{ __('erp.balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php
                            $code = is_object($row) ? $row->code : ($row['code'] ?? '');
                            $name = is_object($row) ? $row->nameAr : ($row['name_ar'] ?? '');
                            $debit = is_object($row) ? $row->debit : ($row['debit'] ?? '0');
                            $credit = is_object($row) ? $row->credit : ($row['credit'] ?? '0');
                            $balance = is_object($row) ? $row->balance : ($row['balance'] ?? '0');
                        @endphp
                        <tr wire:key="tb-{{ $code }}">
                            <td class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $code }}</td>
                            <td class="text-foreground">{{ $name }}</td>
                            <td class="text-end"><x-ui.money :amount="$debit" :muted="(float) $debit == 0" /></td>
                            <td class="text-end"><x-ui.money :amount="$credit" :muted="(float) $credit == 0" /></td>
                            <td class="text-end"><x-ui.money :amount="$balance" :negative="(float) $balance < 0" /></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="!text-end">{{ __('erp.total') }}</td>
                        <td class="text-end"><x-ui.money :amount="$totals['debit'] ?? '0'" /></td>
                        <td class="text-end"><x-ui.money :amount="$totals['credit'] ?? '0'" /></td>
                        <td class="text-end">
                            <x-ui.badge :variant="$balanced ? 'success' : 'danger'">{{ $balanced ? __('erp.balanced') : __('erp.unbalanced') }}</x-ui.badge>
                        </td>
                    </tr>
                </tfoot>
            </x-ui.table>
        @endif
    </x-ui.report-shell>
</div>
