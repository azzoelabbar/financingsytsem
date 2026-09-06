<div>
    <x-ui.entity-header
        :title="$investment->name"
        :eyebrow="__('erp.investment.title')"
        :subtitle="__('erp.code').': '.$investment->code"
        :code="$investment->code"
        :status="$investment->status?->value ?? $investment->status"
        :breadcrumbs="[
            ['label' => __('erp.nav.investments'), 'href' => route('investments.index')],
            ['label' => $investment->code],
        ]"
    >
        <x-slot:badges>
            <x-ui.badge variant="outline" size="md">{{ __('erp.investment.classifications.'.($investment->classification?->value ?? $investment->classification)) }}</x-ui.badge>
        </x-slot:badges>

        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('investments.index')">{{ __('erp.action.back') }}</x-ui.button>
            @if ($investment->journal_id)
                <x-ui.button variant="secondary" :href="route('gl.journals.show', $investment->journal_id)">{{ __('erp.document.view_journal') }}</x-ui.button>
            @endif
        </x-slot:actions>

        <x-slot:metrics>
            <x-ui.metric :label="__('erp.investment.cost')"><x-ui.money :amount="$investment->cost ?? $investment->acquisition_cost ?? '0'" /></x-ui.metric>
            <x-ui.metric :label="__('erp.investment.carrying')"><x-ui.money :amount="$investment->carrying_amount ?? $investment->carrying ?? '0'" /></x-ui.metric>
            <x-ui.metric :label="__('erp.investment.fair_value')"><x-ui.money :amount="$investment->fair_value ?? '0'" /></x-ui.metric>
            <x-ui.metric :label="__('erp.investment.acquired')">
                <span class="tabular-nums" dir="ltr">{{ $investment->acquired_at?->format('Y-m-d') ?? '-' }}</span>
            </x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>
    @error('action') <div class="mb-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
    @if (($investment->status?->value ?? $investment->status) === 'active')
        <x-ui.card :title="__('erp.investment.actions')" class="mb-6">
            <x-ui.field class="max-w-sm" :label="__('erp.date')" for="investment-action-date" required :error="$errors->first('actionDate')"><input id="investment-action-date" type="date" wire:model="actionDate" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.tabs class="mt-5" name="investmentAction" :tabs="[
                ['key' => 'revalue', 'label' => __('erp.investment.revalue')],
                ['key' => 'income', 'label' => __('erp.investment.record_income')],
                ['key' => 'dispose', 'label' => __('erp.investment.dispose')],
            ]">
                <form wire:submit="revalue" x-show="investmentAction === 'revalue'" class="max-w-sm">
                    <x-ui.field :label="__('erp.investment.new_fair_value')" for="fair-value" required :error="$errors->first('fairValue')"><input id="fair-value" type="number" min="0.000001" step="0.000001" wire:model="fairValue" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
                    <div class="mt-4"><x-ui.button type="submit">{{ __('erp.investment.revalue') }}</x-ui.button></div>
                </form>
                <form wire:submit="recordIncome" x-show="investmentAction === 'income'" x-cloak class="max-w-sm">
                    <x-ui.field :label="__('erp.investment.income_amount')" for="income-amount" required :error="$errors->first('incomeAmount')"><input id="income-amount" type="number" min="0.000001" step="0.000001" wire:model="incomeAmount" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
                    <div class="mt-4"><x-ui.button type="submit">{{ __('erp.investment.record_income') }}</x-ui.button></div>
                </form>
                <form wire:submit="dispose" x-show="investmentAction === 'dispose'" x-cloak class="max-w-xl">
                    <p class="mb-4 text-sm text-[var(--danger)]">{{ __('erp.mizan.disposal_hint') }}</p>
                    <x-ui.field class="max-w-sm" :label="__('erp.investment.proceeds')" for="investment-proceeds" required :error="$errors->first('proceeds')"><input id="investment-proceeds" type="number" min="0.000001" step="0.000001" wire:model="proceeds" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
                    <div class="mt-4"><x-ui.button type="submit" variant="danger">{{ __('erp.investment.dispose') }}</x-ui.button></div>
                </form>
            </x-ui.tabs>
        </x-ui.card>
    @endif
    <div class="grid gap-5 lg:grid-cols-3">
        <x-ui.card :title="__('erp.investment.valuations')"><div class="space-y-2">@forelse($investment->valuations as $row)<div class="flex items-center justify-between gap-3 border-b border-border pb-2"><span class="tabular-nums" dir="ltr">{{ $row->valued_at?->format('Y-m-d') }}</span><x-ui.money :amount="$row->fair_value" />@if($row->journal_id)<a href="{{ route('gl.journals.show', $row->journal_id) }}" wire:navigate class="text-xs font-medium text-[var(--brand-600)] hover:underline">#{{ $row->journal_id }}</a>@endif</div>@empty<p class="text-sm text-muted-foreground">{{ __('erp.no_data') }}</p>@endforelse</div></x-ui.card>
        <x-ui.card :title="__('erp.investment.income')"><div class="space-y-2">@forelse($investment->incomes as $row)<div class="flex items-center justify-between gap-3 border-b border-border pb-2"><span class="tabular-nums" dir="ltr">{{ $row->received_at?->format('Y-m-d') ?? $row->income_date?->format('Y-m-d') }}</span><x-ui.money :amount="$row->amount" />@if($row->journal_id)<a href="{{ route('gl.journals.show', $row->journal_id) }}" wire:navigate class="text-xs font-medium text-[var(--brand-600)] hover:underline">#{{ $row->journal_id }}</a>@endif</div>@empty<p class="text-sm text-muted-foreground">{{ __('erp.no_data') }}</p>@endforelse</div></x-ui.card>
        <x-ui.card :title="__('erp.investment.disposals')"><div class="space-y-2">@forelse($investment->disposals as $row)<div class="flex items-center justify-between gap-3 border-b border-border pb-2"><span class="tabular-nums" dir="ltr">{{ $row->disposed_at?->format('Y-m-d') }}</span><x-ui.money :amount="$row->proceeds" />@if($row->journal_id)<a href="{{ route('gl.journals.show', $row->journal_id) }}" wire:navigate class="text-xs font-medium text-[var(--brand-600)] hover:underline">#{{ $row->journal_id }}</a>@endif</div>@empty<p class="text-sm text-muted-foreground">{{ __('erp.no_data') }}</p>@endforelse</div></x-ui.card>
    </div>
</div>
