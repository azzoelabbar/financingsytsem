<div>
    <x-ui.entity-header
        :title="$asset->name"
        :eyebrow="__('erp.assets.title')"
        :subtitle="__('erp.code').': '.$asset->code"
        :code="$asset->code"
        :status="$asset->status"
        :tone="($asset->status?->value ?? $asset->status) === 'disposed' ? 'neutral' : 'brand'"
        :breadcrumbs="[
            ['label' => __('erp.nav.fixed_assets')],
            ['label' => __('erp.nav.assets'), 'href' => route('assets.index')],
            ['label' => $asset->code],
        ]"
    >
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('assets.index')">{{ __('erp.action.back') }}</x-ui.button>
            @if ($asset->acquisition_journal_id)
                <x-ui.button variant="secondary" :href="route('gl.journals.show', $asset->acquisition_journal_id)">{{ __('erp.document.view_journal') }}</x-ui.button>
            @endif
        </x-slot:actions>

        <x-slot:metrics>
            <x-ui.metric :label="__('erp.assets.cost')">
                <x-ui.money :amount="$asset->cost" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.assets.accum_dep')">
                <x-ui.money :amount="$asset->accum_depreciation" :muted="(float) $asset->accum_depreciation == 0" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.assets.nbv')">
                <x-ui.money :amount="$asset->netBookValue()" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.assets.in_service')">
                <span class="tabular-nums" dir="ltr">{{ $asset->in_service_date?->format('Y-m-d') ?? '-' }}</span>
            </x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>

    @error('action') <div class="mb-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
    @if (($asset->status?->value ?? $asset->status) === 'active')
        <x-ui.card :title="__('erp.assets.actions')" class="mb-6">
            <x-ui.field class="max-w-sm" :label="__('erp.date')" for="asset-action-date" required :error="$errors->first('actionDate')"><input id="asset-action-date" type="date" wire:model="actionDate" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.tabs class="mt-5" name="assetAction" :tabs="[
                ['key' => 'depreciate', 'label' => __('erp.assets.post_depreciation')],
                ['key' => 'dispose', 'label' => __('erp.assets.dispose')],
            ]">
                <form wire:submit="depreciate" x-show="assetAction === 'depreciate'"><x-ui.button type="submit">{{ __('erp.assets.post_depreciation') }}</x-ui.button></form>
                <form wire:submit="dispose" x-show="assetAction === 'dispose'" x-cloak class="max-w-xl">
                    <p class="mb-4 text-sm text-[var(--danger)]">{{ __('erp.mizan.disposal_hint') }}</p>
                    <x-ui.field class="max-w-sm" :label="__('erp.assets.disposal_proceeds')" for="asset-proceeds" required :error="$errors->first('disposalProceeds')"><input id="asset-proceeds" type="number" min="0" step="0.000001" wire:model="disposalProceeds" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
                    <div class="mt-4"><x-ui.button type="submit" variant="danger">{{ __('erp.assets.dispose') }}</x-ui.button></div>
                </form>
            </x-ui.tabs>
        </x-ui.card>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card :title="__('erp.assets.identity')">
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div><dt class="text-xs text-muted-foreground">{{ __('erp.code') }}</dt><dd class="mt-0.5 text-sm font-medium" dir="ltr">{{ $asset->code }}</dd></div>
                    <div><dt class="text-xs text-muted-foreground">{{ __('erp.assets.name') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ $asset->name }}</dd></div>
                    <div><dt class="text-xs text-muted-foreground">{{ __('erp.assets.in_service') }}</dt><dd class="mt-0.5 text-sm font-medium tabular-nums" dir="ltr">{{ $asset->in_service_date?->format('Y-m-d') }}</dd></div>
                    <div><dt class="text-xs text-muted-foreground">{{ __('erp.assets.useful_life') }}</dt><dd class="mt-0.5 text-sm font-medium tabular-nums" dir="ltr">{{ $asset->useful_life_months }} {{ __('erp.assets.months') }}</dd></div>
                    <div><dt class="text-xs text-muted-foreground">{{ __('erp.assets.location') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ $asset->location ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-muted-foreground">{{ __('erp.book') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ $asset->book?->code }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card :title="__('erp.assets.valuation')">
                <dl class="space-y-3">
                    <div class="flex items-center justify-between text-sm"><dt class="text-muted-foreground">{{ __('erp.assets.cost') }}</dt><dd class="font-medium"><x-ui.money :amount="$asset->cost" /></dd></div>
                    <div class="flex items-center justify-between text-sm"><dt class="text-muted-foreground">{{ __('erp.assets.accum_dep') }}</dt><dd><x-ui.money :amount="$asset->accum_depreciation" muted /></dd></div>
                    @if ((float) $asset->accum_impairment != 0)
                        <div class="flex items-center justify-between text-sm"><dt class="text-muted-foreground">{{ __('erp.assets.impairment') }}</dt><dd><x-ui.money :amount="$asset->accum_impairment" muted /></dd></div>
                    @endif
                    <div class="flex items-center justify-between border-t border-border pt-3 text-base font-semibold"><dt>{{ __('erp.assets.nbv') }}</dt><dd><x-ui.money :amount="$asset->netBookValue()" /></dd></div>
                </dl>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card :title="__('erp.document.accounting')">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-muted-foreground">{{ __('erp.assets.cost_account') }}</dt><dd class="font-mono text-xs" dir="ltr">{{ $asset->cost_account_code }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">{{ __('erp.assets.accum_account') }}</dt><dd class="font-mono text-xs" dir="ltr">{{ $asset->accum_account_code }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">{{ __('erp.assets.expense_account') }}</dt><dd class="font-mono text-xs" dir="ltr">{{ $asset->expense_account_code }}</dd></div>
                    @if ($asset->acquisition_journal_id)
                        <div class="flex justify-between border-t border-border pt-3"><dt class="text-muted-foreground">{{ __('erp.assets.acquisition_journal') }}</dt><dd><a href="{{ route('gl.journals.show', $asset->acquisition_journal_id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">#{{ $asset->acquisition_journal_id }}</a></dd></div>
                    @endif
                </dl>
            </x-ui.card>

            <x-ui.card :title="__('erp.assets.movement_journals')">
                <div class="divide-y divide-border">
                    @forelse ($journals as $journal)
                        <a href="{{ route('gl.journals.show', $journal) }}" wire:navigate class="flex items-center justify-between gap-3 py-2 text-sm hover:text-[var(--brand-600)]">
                            <span class="font-medium" dir="ltr">{{ $journal->number }}</span>
                            <span class="tabular-nums text-muted-foreground" dir="ltr">{{ $journal->posting_date?->format('Y-m-d') }}</span>
                            <x-ui.money :amount="$journal->total_debit" />
                        </a>
                    @empty
                        <p class="text-sm text-muted-foreground">{{ __('erp.no_data') }}</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
