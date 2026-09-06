@props([
    'title' => null,
    'message' => null,
])

{{--
    Immutability banner for posted documents. Deliberately unmissable: once a
    document is posted it can only be corrected by another document.
--}}
<div {{ $attributes->merge(['class' => 'mb-5 flex items-start gap-3 rounded-lg border px-4 py-3.5']) }}
     style="border-color: var(--info-line); background: var(--brand-50);">
    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[var(--brand-600)] text-white">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 1a4 4 0 00-4 4v2H5.5A1.5 1.5 0 004 8.5v8A1.5 1.5 0 005.5 18h9a1.5 1.5 0 001.5-1.5v-8A1.5 1.5 0 0014.5 7H14V5a4 4 0 00-4-4zm2 6V5a2 2 0 10-4 0v2h4z" clip-rule="evenodd" />
        </svg>
    </span>
    <div class="min-w-0">
        <p class="text-[0.8125rem] font-semibold text-[var(--brand-900)]">{{ $title ?? __('erp.document.immutable_title') }}</p>
        <p class="mt-0.5 text-[0.8125rem] leading-relaxed text-[var(--brand-900)]/80">{{ $message ?? __('erp.document.immutable_notice') }}</p>
    </div>
</div>
