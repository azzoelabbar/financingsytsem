<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.fixed_assets')], ['label' => __('erp.nav.assets')]]" :title="__('erp.assets.title')" :description="__('erp.assets.hint')"><x-slot:actions><x-ui.button :href="route('assets.create')">{{ __('erp.assets.create') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex rounded-md border border-border bg-card p-0.5 text-sm">
            @foreach (['' => __('erp.assets.all'), 'active' => __('erp.assets.status_active'), 'disposed' => __('erp.assets.status_disposed')] as $val => $label)
                <button type="button" wire:click="$set('status', '{{ $val }}')" @class([
                    'rounded px-3 py-1 font-medium transition-colors',
                    'bg-[var(--ink)] text-white' => $status === $val,
                    'text-muted-foreground hover:text-foreground' => $status !== $val,
                ])>{{ $label }}</button>
            @endforeach
        </div>
        <div class="flex-1"></div>
    </div>

    <x-ui.toolbar :summary="$assets ? trans_choice('erp.pagination.result_count', $assets->total(), ['count' => number_format($assets->total())]) : null" />

    @if ($assets === null || $assets->isEmpty())
        <x-ui.empty-state :title="__('erp.assets.empty_title')" :message="__('erp.assets.empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.code') }}</th>
                <th>{{ __('erp.assets.name') }}</th>
                <th>{{ __('erp.assets.in_service') }}</th>
                <th class="!text-end">{{ __('erp.assets.cost') }}</th>
                <th class="!text-end">{{ __('erp.assets.accum_dep') }}</th>
                <th class="!text-end">{{ __('erp.assets.nbv') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($assets as $asset)
                    <tr wire:key="fa-{{ $asset->id }}">
                        <td><a href="{{ route('assets.show', $asset->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $asset->code }}</a></td>
                        <td>{{ $asset->name }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $asset->in_service_date?->format('Y-m-d') }}</td>
                        <td class="text-end"><x-ui.money :amount="$asset->cost" /></td>
                        <td class="text-end"><x-ui.money :amount="$asset->accum_depreciation" muted /></td>
                        <td class="text-end"><x-ui.money :amount="$asset->netBookValue()" /></td>
                        <td><x-ui.status-badge :status="$asset->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$assets" />
    @endif
</div>
