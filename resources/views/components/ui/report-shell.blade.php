@props([
    'title',
    'description' => null,
    'company' => null,
    'book' => null,
    'period' => null,
    'asOf' => null,
    'breadcrumbs' => [],
    // Every report is downloadable; set false only where the screen has no exportExcel action.
    'export' => true,
])

@php
    $basisTone = match ($book?->code) {
        'IFRS' => 'info',
        'TAX' => 'warning',
        default => 'primary',
    };
    $basisLabel = match ($book?->code) {
        'IFRS' => __('erp.book_basis.ifrs'),
        'TAX' => __('erp.book_basis.tax'),
        'LOCAL' => __('erp.book_basis.local'),
        default => $book?->code ?? '-',
    };
    $companyName = $company
        ? (app()->getLocale() === 'ar' ? ($company->name_ar ?? $company->code) : ($company->name_en ?? $company->name_ar ?? $company->code))
        : '-';
@endphp

{{--
    Financial-report chrome. Everything above the statement body states the
    basis of preparation, so a printed or shared page is self-describing:
    which entity, which book, which period, as at which date.
--}}
<div {{ $attributes }}>
    <x-ui.page-header :title="$title" :description="$description" :breadcrumbs="$breadcrumbs" :eyebrow="__('erp.nav.reports')" class="erp-no-print">
        @if (isset($filters) || $export)
            <x-slot:actions>
                @isset($filters){{ $filters }}@endisset
                @if ($export)
                    <x-ui.export-button />
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <div class="mizan-report-identity mb-6 overflow-hidden border border-border bg-card">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-border px-6 py-5">
            <div class="flex min-w-0 items-center gap-4">
                <x-brand.mark class="h-11 w-11" />
                <div class="min-w-0">
                <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.09em] text-[var(--brand-700)]" dir="ltr">{{ $company?->code ?? '' }}</p>
                <p class="break-words text-base font-semibold text-foreground">{{ $companyName }}</p>
                <p class="hidden text-xl font-semibold print:block">{{ $title }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground">{{ __('erp.report_basis') }}</span>
                <x-ui.badge :variant="$basisTone" size="md">{{ $basisLabel }}</x-ui.badge>
            </div>
        </div>
        <dl class="grid grid-cols-2 gap-px bg-border sm:grid-cols-4">
            <div class="bg-card px-5 py-2.5">
                <dt class="text-[0.6875rem] uppercase tracking-wide text-muted-foreground">{{ __('erp.book') }}</dt>
                <dd class="mt-0.5 text-[0.8125rem] font-semibold">{{ $book?->code ?? '-' }}</dd>
            </div>
            <div class="bg-card px-5 py-2.5">
                <dt class="text-[0.6875rem] uppercase tracking-wide text-muted-foreground">{{ __('erp.period') }}</dt>
                <dd class="mt-0.5 text-[0.8125rem] font-semibold">{{ $period?->start_date?->locale(app()->getLocale())->translatedFormat('M Y') ?? $period?->period_no ?? '-' }}</dd>
            </div>
            <div class="bg-card px-5 py-2.5">
                <dt class="text-[0.6875rem] uppercase tracking-wide text-muted-foreground">{{ __('erp.currency') }}</dt>
                <dd class="mt-0.5 text-[0.8125rem] font-semibold" dir="ltr">{{ $company?->functional_currency ?? '-' }}</dd>
            </div>
            <div class="bg-card px-5 py-2.5">
                <dt class="text-[0.6875rem] uppercase tracking-wide text-muted-foreground">{{ __('erp.report_date') }}</dt>
                <dd class="mt-0.5 text-[0.8125rem] font-semibold tabular-nums" dir="ltr">{{ $asOf ?? now()->toDateString() }}</dd>
            </div>
        </dl>
    </div>

    <div class="mizan-report-body">{{ $slot }}</div>
</div>
