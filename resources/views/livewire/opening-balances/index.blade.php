<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.opening_balances')]]" :title="__('erp.opening.title')" :description="__('erp.opening.hint')"><x-slot:actions><x-ui.button :href="route('opening-balances.create')">{{ __('erp.opening.create') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    @if ($batches === null || $batches->isEmpty())
        <x-ui.empty-state :title="__('erp.opening.empty_title')" :message="__('erp.opening.empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.opening.as_of') }}</th>
                <th>{{ __('erp.currency') }}</th>
                <th>{{ __('erp.status') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
                @foreach ($batches as $batch)
                    <tr wire:key="ob-{{ $batch->id }}">
                        <td class="font-medium tabular-nums" dir="ltr">{{ $batch->as_of?->format('Y-m-d') }}</td>
                        <td>{{ $batch->currency }}</td>
                        <td><x-ui.status-badge :status="$batch->status" /></td>
                        <td class="text-end"><a href="{{ route('opening-balances.show', $batch->id) }}" wire:navigate class="text-sm font-medium text-[var(--brand-600)] hover:underline">{{ __('erp.view_all') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$batches" />
    @endif
</div>
