<div>
    @php
        $balanced = $journal->isBalanced();
        $posted = ($journal->status?->value ?? $journal->status) === 'posted';
    @endphp

    <x-ui.entity-header
        :title="$journal->number"
        :eyebrow="__('erp.journal.entity')"
        :subtitle="$journal->description ?? null"
        code="JV"
        :status="$journal->status"
        :tone="$posted ? 'brand' : 'neutral'"
        :breadcrumbs="[
            ['label' => __('erp.nav.gl')],
            ['label' => __('erp.nav.journals'), 'href' => route('gl.journals')],
            ['label' => $journal->number],
        ]"
    >
        <x-slot:badges>
            <x-ui.badge :variant="$balanced ? 'success' : 'danger'" size="md">
                {{ $balanced ? __('erp.balanced') : __('erp.unbalanced') }}
            </x-ui.badge>
        </x-slot:badges>

        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('gl.journals')">{{ __('erp.action.back') }}</x-ui.button>
        </x-slot:actions>

        <x-slot:metrics>
            <x-ui.metric :label="__('erp.debit')">
                <x-ui.money :amount="$journal->total_debit ?? '0'" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.credit')">
                <x-ui.money :amount="$journal->total_credit ?? '0'" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.journal.journal_date')">
                <span class="tabular-nums" dir="ltr">{{ $journal->journal_date?->format('Y-m-d') }}</span>
            </x-ui.metric>
            <x-ui.metric :label="__('erp.journal.line_count')">
                <span class="tabular-nums">{{ $journal->lines->count() }}</span>
            </x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>

    @if ($posted)
        <x-ui.posted-notice :message="__('erp.journal.immutable_notice')" />
    @endif

    @if ($journal->lines->isEmpty())
        <x-ui.empty-state :title="__('erp.journal.no_lines_title')" :message="__('erp.journal.no_lines_hint')" />
    @else
        <x-ui.card :title="__('erp.journal.entry_lines')" :description="__('erp.journal.entry_lines_hint')" flush>
            <x-ui.table flush density="compact">
                <thead>
                    <tr>
                        <th>{{ __('erp.journal.account') }}</th>
                        <th>{{ __('erp.journal.description') }}</th>
                        <th class="!text-end">{{ __('erp.debit') }}</th>
                        <th class="!text-end">{{ __('erp.credit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($journal->lines as $line)
                        <tr wire:key="jl-{{ $line->id }}">
                            <td>
                                <span class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $line->account?->code }}</span>
                                <span class="ms-2 text-foreground">{{ $line->account?->name_ar }}</span>
                            </td>
                            <td class="text-muted-foreground">{{ $line->description ?? '-' }}</td>
                            <td class="text-end"><x-ui.money :amount="$line->debit" :muted="(float) $line->debit == 0" /></td>
                            <td class="text-end"><x-ui.money :amount="$line->credit" :muted="(float) $line->credit == 0" /></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="!text-end">{{ __('erp.total') }}</td>
                        <td class="text-end"><x-ui.money :amount="$journal->total_debit ?? '0'" /></td>
                        <td class="text-end"><x-ui.money :amount="$journal->total_credit ?? '0'" /></td>
                    </tr>
                </tfoot>
            </x-ui.table>
        </x-ui.card>
    @endif
</div>
