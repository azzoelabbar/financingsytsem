@props(['kind'])
@if (app(\App\Support\Accounting\AccountingContext::class)->can(\App\Support\Import\ImportCatalog::permission($kind)))
    <x-ui.button variant="secondary" :href="route('imports.create', $kind)">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 16V3m-5 5 5-5 5 5M4 15v5a1 1 0 001 1h14a1 1 0 001-1v-5" /></svg>
        {{ __('imports.button') }}
    </x-ui.button>
@endif
