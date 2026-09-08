<div>
    @php $balanced = abs((float) $difference) < 0.0000001; @endphp
    <x-ui.entity-header
        :title="__('erp.opening.batch').' '.$batch->as_of?->format('Y-m-d')"
        :eyebrow="__('erp.opening.title')"
        :code="'OB'"
        :status="$status"
        :tone="$balanced ? 'brand' : 'danger'"
        :breadcrumbs="[
            ['label' => __('erp.nav.opening_balances'), 'href' => route('opening-balances.index')],
            ['label' => $batch->as_of?->format('Y-m-d') ?? ''],
        ]"
    >
        <x-slot:badges>
            <x-ui.badge :variant="$balanced ? 'success' : 'danger'" size="md">{{ $balanced ? __('erp.balanced') : __('erp.unbalanced') }}</x-ui.badge>
        </x-slot:badges>

        <x-slot:actions>
            <x-ui.export-button />
            <x-ui.button variant="ghost" :href="route('opening-balances.index')">{{ __('erp.action.back') }}</x-ui.button>
            @if ($batch->journal_id)
                <x-ui.button variant="secondary" :href="route('gl.journals.show', $batch->journal_id)">{{ __('erp.document.view_journal') }}</x-ui.button>
            @endif
            @if ($status === 'draft')
                <x-ui.button wire:click="validateBatch">{{ __('erp.opening.validate') }}</x-ui.button>
            @elseif ($status === 'validated')
                <x-ui.confirm-action
                    action="postBatch"
                    :label="__('erp.action.post')"
                    :title="__('erp.opening.post_confirm_title')"
                    :message="__('erp.opening.post_confirm_body')"
                    :confirm-label="__('erp.action.confirm_post')"
                />
            @elseif ($status === 'posted')
                <x-ui.confirm-action
                    action="lockBatch"
                    variant="secondary"
                    :label="__('erp.opening.lock')"
                    :title="__('erp.opening.lock_confirm_title')"
                    :message="__('erp.opening.lock_confirm_body')"
                />
            @endif
        </x-slot:actions>
    </x-ui.entity-header>

    @error('action')<x-ui.alert variant="danger" class="mb-5">{{ $message }}</x-ui.alert>@enderror
    @if ($status === 'draft')
        <form wire:submit="addLine" class="mb-5"><x-ui.card :title="__('erp.opening.add_line')"><div class="grid items-end gap-4 md:grid-cols-4"><x-ui.searchable-select :label="__('erp.account')" required :error="$errors->first('accountCode')" id="opening-account" wire:model="accountCode"><option value="">{{ __('erp.select') }}</option>@foreach($accounts as $account)<option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale()==='ar'?$account->name_ar:($account->name_en??$account->name_ar) }}</option>@endforeach</x-ui.searchable-select><x-ui.searchable-select id="opening-side" wire:model="side" :label="__('erp.opening.side')" required><option value="debit">{{ __('erp.debit') }}</option><option value="credit">{{ __('erp.credit') }}</option></x-ui.searchable-select><x-ui.field :label="__('erp.amount')" for="opening-amount" required :error="$errors->first('amount')"><input id="opening-amount" type="number" min="0.000001" step="0.000001" wire:model="amount" class="erp-control text-end tabular-nums" dir="ltr"/></x-ui.field><x-ui.button type="submit">{{ __('erp.opening.add_line') }}</x-ui.button></div></x-ui.card></form>
    @endif

    {{-- Whether the batch balances decides whether it can be posted at all. --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border px-5 py-3.5"
         style="border-color: {{ $balanced ? 'var(--success-line)' : 'var(--danger-line)' }}; background: {{ $balanced ? 'var(--success-muted)' : 'var(--danger-muted)' }}">
        <div class="flex items-center gap-2.5">
            <span class="text-[0.8125rem] font-semibold text-foreground">{{ $balanced ? __('erp.opening.balanced_title') : __('erp.opening.unbalanced_title') }}</span>
            <x-ui.badge :variant="$balanced ? 'success' : 'danger'">{{ $balanced ? __('erp.balanced') : __('erp.unbalanced') }}</x-ui.badge>
        </div>
        <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-[0.8125rem]">
            <span class="flex items-center gap-2"><span class="text-muted-foreground">{{ __('erp.debit') }}</span><x-ui.money :amount="$totalDebit" size="lg" /></span>
            <span class="flex items-center gap-2"><span class="text-muted-foreground">{{ __('erp.credit') }}</span><x-ui.money :amount="$totalCredit" size="lg" /></span>
            @unless ($balanced)
                <span class="flex items-center gap-2"><span class="text-muted-foreground">{{ __('erp.reconciliation.difference_amount') }}</span><x-ui.money :amount="$difference" tone="danger" size="lg" /></span>
            @endunless
        </div>
    </div>

    <x-ui.card :title="__('erp.opening.lines')" :description="__('erp.opening.lines_hint')" flush>
        <x-ui.table flush density="compact">
            <thead>
                <tr>
                    <th>{{ __('erp.code') }}</th>
                    <th class="!text-end">{{ __('erp.debit') }}</th>
                    <th class="!text-end">{{ __('erp.credit') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batch->lines as $line)
                    <tr wire:key="ob-line-{{ $line->id }}">
                        <td class="font-mono text-xs text-foreground" dir="ltr">{{ $line->account_code }}</td>
                        <td class="text-end"><x-ui.money :amount="$line->debit" :muted="(float) $line->debit == 0" /></td>
                        <td class="text-end"><x-ui.money :amount="$line->credit" :muted="(float) $line->credit == 0" /></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="!py-6 text-center text-muted-foreground">{{ __('erp.no_data') }}</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>{{ __('erp.total') }}</td>
                    <td class="text-end"><x-ui.money :amount="$totalDebit" /></td>
                    <td class="text-end"><x-ui.money :amount="$totalCredit" /></td>
                </tr>
            </tfoot>
        </x-ui.table>
    </x-ui.card>
</div>
