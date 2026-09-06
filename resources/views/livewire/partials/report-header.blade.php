@props(['company', 'book', 'period' => null, 'asOf' => null])

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

<div class="mb-6 overflow-hidden rounded-lg border border-border bg-card">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-border bg-surface-sunken px-5 py-4">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ $company?->code ?? '' }}</p>
            <p class="truncate text-lg font-semibold text-foreground">{{ $companyName }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground">{{ __('erp.report_basis') }}</span>
            <x-ui.badge :variant="$basisTone">{{ $basisLabel }}</x-ui.badge>
        </div>
    </div>
    <dl class="grid grid-cols-2 divide-x divide-border rtl:divide-x-reverse sm:grid-cols-4 [&>div]:px-5 [&>div]:py-3">
        <div>
            <dt class="text-xs text-muted-foreground">{{ __('erp.book') }}</dt>
            <dd class="mt-0.5 text-sm font-semibold">{{ $book?->code ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted-foreground">{{ __('erp.period') }}</dt>
            <dd class="mt-0.5 text-sm font-semibold">{{ $period?->period_no ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted-foreground">{{ __('erp.currency') }}</dt>
            <dd class="mt-0.5 text-sm font-semibold">{{ $company?->functional_currency ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted-foreground">{{ __('erp.report_date') }}</dt>
            <dd class="mt-0.5 text-sm font-semibold tabular-nums" dir="ltr">{{ $asOf ?? now()->toDateString() }}</dd>
        </div>
    </dl>
</div>
