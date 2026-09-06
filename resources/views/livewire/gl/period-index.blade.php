<div>
    <x-ui.page-header
        :title="__('erp.nav.periods')"
        :description="__('erp.list.periods_hint')"
        :breadcrumbs="[['label' => __('erp.nav.gl')], ['label' => __('erp.nav.periods')]]"
    />

    @error('period')<x-ui.alert variant="danger" class="mb-5">{{ $message }}</x-ui.alert>@enderror
    <x-ui.card :title="__('erp.period_page.reopen_control')" class="mb-5"><x-ui.field :label="__('erp.period_page.reopen_reason')" for="reopen-reason" :error="$errors->first('reopenReason')"><input id="reopen-reason" wire:model="reopenReason" class="erp-control"/></x-ui.field></x-ui.card>

    <x-ui.toolbar
        :placeholder="__('erp.period_page.search_placeholder')"
        :summary="$periods ? trans_choice('erp.pagination.result_count', $periods->total(), ['count' => number_format($periods->total())]) : null"
    />

    @if ($periods === null || $periods->isEmpty())
        <x-ui.empty-state
            :title="$search !== '' ? __('erp.filter.no_matches_title') : __('erp.period_page.empty_title')"
            :message="$search !== '' ? __('erp.filter.no_matches_hint') : __('erp.period_page.empty_hint')"
        >
            @if ($search !== '')
                <x-slot:actions>
                    <x-ui.button variant="secondary" wire:click="$set('search', '')">{{ __('erp.filter.clear') }}</x-ui.button>
                </x-slot:actions>
            @endif
        </x-ui.empty-state>
    @else
        <x-ui.card :title="__('erp.period_page.card_title')" :description="__('erp.period_page.card_hint')" flush>
            <x-ui.table flush>
                <thead>
                    <tr>
                        <th>{{ __('erp.period') }}</th>
                        <th>{{ __('erp.period_page.range') }}</th>
                        <th>{{ __('erp.status') }}</th>
                        <th>{{ __('erp.period_page.posting') }}</th><th>{{ __('erp.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($periods as $period)
                        @php
                            $status = $period->status?->value ?? $period->status;
                            $allowsPosting = $status === 'open';
                        @endphp
                        <tr wire:key="period-{{ $period->id }}">
                            <td class="font-medium tabular-nums text-foreground">{{ $period->period_no }}</td>
                            <td class="tabular-nums text-muted-foreground" dir="ltr">
                                {{ $period->start_date?->format('Y-m-d') }} - {{ $period->end_date?->format('Y-m-d') }}
                            </td>
                            <td><x-ui.status-badge :status="$status" /></td>
                            <td>
                                <span class="text-[0.8125rem] {{ $allowsPosting ? 'text-[var(--success-color)]' : 'text-muted-foreground' }}">
                                    {{ $allowsPosting ? __('erp.period_page.posting_allowed') : __('erp.period_page.posting_blocked') }}
                                </span>
                            </td>
                            <td><div class="flex flex-wrap gap-2">@if($status==='open')<x-ui.button size="sm" variant="secondary" wire:click="changeStatus({{ $period->id }}, 'soft_closed')">{{ __('erp.period_page.soft_close') }}</x-ui.button><x-ui.button size="sm" variant="danger" wire:click="changeStatus({{ $period->id }}, 'hard_closed')">{{ __('erp.period_page.hard_close') }}</x-ui.button>@elseif($status==='soft_closed')<x-ui.button size="sm" variant="danger" wire:click="changeStatus({{ $period->id }}, 'hard_closed')">{{ __('erp.period_page.hard_close') }}</x-ui.button><x-ui.button size="sm" variant="secondary" wire:click="changeStatus({{ $period->id }}, 'open')">{{ __('erp.period_page.reopen') }}</x-ui.button>@elseif($status==='hard_closed')<x-ui.button size="sm" variant="danger" wire:click="changeStatus({{ $period->id }}, 'locked')">{{ __('erp.period_page.lock') }}</x-ui.button><x-ui.button size="sm" variant="secondary" wire:click="changeStatus({{ $period->id }}, 'open')">{{ __('erp.period_page.reopen') }}</x-ui.button>@else<span class="text-xs text-muted-foreground">{{ __('erp.period_page.terminal') }}</span>@endif</div></td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </x-ui.card>

        <x-ui.pagination :paginator="$periods" />
    @endif
</div>
