@php
    $settingsLink = fn (string $route): string => request()->routeIs($route) ? 'bg-[var(--teal-muted)] font-medium text-[var(--teal)]' : 'text-muted-foreground hover:bg-muted hover:text-foreground';
@endphp

<nav class="mb-6 flex flex-wrap gap-1 rounded-lg border border-border bg-card p-1">
    <a href="{{ route('profile.edit') }}" class="rounded-md px-4 py-2 text-sm transition-colors {{ $settingsLink('profile.edit') }}">
        {{ __('erp.settings.profile') }}
    </a>
    <a href="{{ route('security.edit') }}" class="rounded-md px-4 py-2 text-sm transition-colors {{ $settingsLink('security.edit') }}">
        {{ __('erp.settings.security') }}
    </a>
    <a href="{{ route('appearance.edit') }}" class="rounded-md px-4 py-2 text-sm transition-colors {{ $settingsLink('appearance.edit') }}">
        {{ __('erp.settings.appearance') }}
    </a>
</nav>
