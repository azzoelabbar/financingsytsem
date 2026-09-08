<div>
    <x-ui.entity-header
        :title="$project->name"
        :eyebrow="__('erp.project.title')"
        :subtitle="__('erp.code').': '.$project->code"
        :code="$project->code"
        :status="$project->status?->value ?? $project->status"
        :breadcrumbs="[
            ['label' => __('erp.nav.projects'), 'href' => route('projects.index')],
            ['label' => $project->code],
        ]"
    >
        <x-slot:actions>
            <x-ui.export-button />
            <x-ui.button variant="ghost" :href="route('projects.index')">{{ __('erp.action.back') }}</x-ui.button>
        </x-slot:actions>

        <x-slot:metrics>
            <x-ui.metric :label="__('erp.project.budget')"><x-ui.money :amount="$budget" /></x-ui.metric>
            <x-ui.metric :label="__('erp.project.charged')"><x-ui.money :amount="$charged" /></x-ui.metric>
            <x-ui.metric :label="__('erp.project.remaining')">
                <x-ui.money :amount="$remaining" :negative="(float) $remaining < 0" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.project.capitalized')"><x-ui.money :amount="$capitalized" /></x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>

    @error('action') <div class="mb-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
    @if (($project->status?->value ?? $project->status) === 'open')
        <div class="mb-6 grid gap-5 lg:grid-cols-2">
            <form wire:submit="charge"><x-ui.card :title="__('erp.project.add_cost')"><div class="grid gap-4 sm:grid-cols-2"><x-ui.field :label="__('erp.date')" for="project-action-date" required><input id="project-action-date" type="date" wire:model="actionDate" class="erp-control" dir="ltr" /></x-ui.field><x-ui.field :label="__('erp.amount')" for="project-cost" required :error="$errors->first('costAmount')"><input id="project-cost" type="number" min="0.000001" step="0.000001" wire:model="costAmount" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field></div><div class="mt-4 text-end"><x-ui.button type="submit">{{ __('erp.project.post_cost') }}</x-ui.button></div></x-ui.card></form>
            <form wire:submit="capitalize"><x-ui.card :title="__('erp.project.capitalize')"><div class="grid gap-4 sm:grid-cols-2"><x-ui.field :label="__('erp.date')" for="project-cap-date" required><input id="project-cap-date" type="date" wire:model="actionDate" class="erp-control" dir="ltr" /></x-ui.field><x-ui.field :label="__('erp.amount')" for="project-cap" required :error="$errors->first('capitalizationAmount')"><input id="project-cap" type="number" min="0.000001" step="0.000001" wire:model="capitalizationAmount" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field></div><div class="mt-4 text-end"><x-ui.button type="submit" variant="secondary">{{ __('erp.project.post_capitalization') }}</x-ui.button></div></x-ui.card></form>
        </div>
    @endif

    @if ($variancePct !== null)
        <div class="mb-6">
            <div class="mb-1 flex items-center justify-between text-xs text-muted-foreground">
                <span>{{ __('erp.project.utilization') }}</span>
                <span class="tabular-nums" dir="ltr">{{ number_format($variancePct, 1) }}%</span>
            </div>
            <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full {{ $variancePct > 100 ? 'bg-[var(--danger)]' : 'bg-[var(--brand-600)]' }}" style="width: {{ min(100, max(0, $variancePct)) }}%"></div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card :title="__('erp.project.costs')" :flush="true">
            @if ($project->costs->isNotEmpty())
                <x-ui.table flush>
                    <thead><tr>
                        <th>{{ __('erp.date') }}</th>
                        <th>{{ __('erp.project.cost_type') }}</th>
                        <th class="!text-end">{{ __('erp.amount') }}</th>
                        <th>{{ __('erp.document.journal') }}</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($project->costs as $cost)
                            <tr wire:key="cost-{{ $cost->id }}">
                                <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $cost->cost_date?->format('Y-m-d') }}</td>
                                <td class="text-muted-foreground">{{ __('erp.project.cost_types.'.($cost->type ?? '')) }}</td>
                                <td class="text-end"><x-ui.money :amount="$cost->amount" /></td>
                                <td>@if ($cost->journal_id)<a href="{{ route('gl.journals.show', $cost->journal_id) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">#{{ $cost->journal_id }}</a>@else <span class="text-muted-foreground">-</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @else
                <div class="p-8 text-center text-sm text-muted-foreground">{{ __('erp.project.no_costs') }}</div>
            @endif
        </x-ui.card>

        <x-ui.card :title="__('erp.project.capitalization')" :flush="true">
            @if ($project->capitalizations->isNotEmpty())
                <x-ui.table flush>
                    <thead><tr>
                        <th>{{ __('erp.date') }}</th>
                        <th class="!text-end">{{ __('erp.amount') }}</th>
                        <th>{{ __('erp.document.journal') }}</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($project->capitalizations as $cap)
                            <tr wire:key="cap-{{ $cap->id }}">
                                <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $cap->capitalized_on?->format('Y-m-d') }}</td>
                                <td class="text-end"><x-ui.money :amount="$cap->amount" /></td>
                                <td>@if ($cap->journal_id)<a href="{{ route('gl.journals.show', $cap->journal_id) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">#{{ $cap->journal_id }}</a>@else <span class="text-muted-foreground">-</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @else
                <div class="p-8 text-center text-sm text-muted-foreground">{{ __('erp.project.no_capitalization') }}</div>
            @endif
        </x-ui.card>
    </div>
</div>
