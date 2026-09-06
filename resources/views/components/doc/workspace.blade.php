@props([
    'title',
    'subtitle' => null,
    // Document kind, shown above the number.
    'eyebrow' => null,
    // Monogram for the identity tile.
    'code' => null,
    'status' => null,
    'backRoute' => null,
    'journalId' => null,
    'bookCode' => null,
    'postingDate' => null,
    'timeline' => [],
    'posted' => false,
    'breadcrumbs' => [],
])

{{--
    Shared financial-document workspace: identity and headline figures at the
    top, the document body in the wide column, and how it hit the ledger in the
    side column. Posted documents announce their immutability before anything
    else on the page.
--}}
<div>
    <x-ui.entity-header
        :title="$title"
        :eyebrow="$eyebrow ?? $subtitle"
        :subtitle="$eyebrow ? $subtitle : null"
        :code="$code"
        :status="$status"
        :tone="$posted ? 'brand' : 'neutral'"
        :breadcrumbs="$breadcrumbs"
    >
        <x-slot:actions>
            @if ($backRoute)
                <x-ui.button variant="ghost" :href="$backRoute">{{ __('erp.action.back') }}</x-ui.button>
            @endif
            @if ($journalId)
                <x-ui.button variant="secondary" :href="route('gl.journals.show', $journalId)">{{ __('erp.document.view_journal') }}</x-ui.button>
            @endif
            {{ $actions ?? '' }}
        </x-slot:actions>

        @isset($metrics)
            <x-slot:metrics>{{ $metrics }}</x-slot:metrics>
        @endisset
    </x-ui.entity-header>

    @if ($posted)
        <x-ui.posted-notice />
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            {{ $slot }}
        </div>

        <div class="space-y-5">
            <x-ui.card :title="__('erp.document.accounting')" :tone="$journalId ? 'brand' : null">
                @if ($journalId)
                    <dl class="divide-y divide-border">
                        <div class="flex items-baseline justify-between gap-3 py-2">
                            <dt class="text-[0.8125rem] text-muted-foreground">{{ __('erp.document.journal') }}</dt>
                            <dd>
                                <a href="{{ route('gl.journals.show', $journalId) }}" wire:navigate class="text-[0.8125rem] font-medium text-[var(--brand-600)] hover:underline" dir="ltr">#{{ $journalId }}</a>
                            </dd>
                        </div>
                        @if ($postingDate)
                            <div class="flex items-baseline justify-between gap-3 py-2">
                                <dt class="text-[0.8125rem] text-muted-foreground">{{ __('erp.document.posting_date') }}</dt>
                                <dd class="text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ $postingDate }}</dd>
                            </div>
                        @endif
                        @if ($bookCode)
                            <div class="flex items-baseline justify-between gap-3 py-2">
                                <dt class="text-[0.8125rem] text-muted-foreground">{{ __('erp.book') }}</dt>
                                <dd class="text-[0.8125rem] font-medium" dir="ltr">{{ $bookCode }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="text-[0.8125rem] leading-relaxed text-muted-foreground">{{ __('erp.document.not_posted_yet') }}</p>
                @endif
            </x-ui.card>

            <x-ui.card :title="__('erp.audit.title')">
                <x-ui.audit-timeline :events="$timeline" />
            </x-ui.card>

            {{ $aside ?? '' }}
        </div>
    </div>
</div>
