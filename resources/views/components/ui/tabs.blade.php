@props([
    // list<array{key: string, label: string, count?: int|string|null}>
    'tabs' => [],
    'default' => null,
    // Alpine state name, so a page can host more than one tab set.
    'name' => 'tab',
])

@php
    $initial = $default ?? ($tabs[0]['key'] ?? '');
@endphp

{{--
    Client-side tabs. Panels are declared by the caller with
    x-show="{{ $name }} === 'key'", so nothing is fetched on switch and the
    whole workspace stays in one Livewire render.
--}}
<div x-data="{ {{ $name }}: '{{ $initial }}' }" {{ $attributes }}>
    <div class="mb-5 min-w-0">
        <div class="mizan-tab-rail" role="tablist"
            @keydown="
                if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes($event.key)) {
                    $event.preventDefault();
                    const buttons = [...$el.querySelectorAll('[role=tab]')];
                    const current = buttons.indexOf($event.target);
                    const forward = document.documentElement.dir === 'rtl' ? 'ArrowLeft' : 'ArrowRight';
                    const index = $event.key === 'Home' ? 0 : $event.key === 'End' ? buttons.length - 1 : (current + ($event.key === forward ? 1 : -1) + buttons.length) % buttons.length;
                    buttons[index]?.click();
                    buttons[index]?.focus();
                }
            "
        >
            @foreach ($tabs as $tab)
                <button
                    type="button"
                    role="tab"
                    :aria-selected="{{ $name }} === '{{ $tab['key'] }}'"
                    :tabindex="{{ $name }} === '{{ $tab['key'] }}' ? 0 : -1"
                    @click="{{ $name }} = '{{ $tab['key'] }}'"
                    class="mizan-tab flex items-center gap-2 whitespace-nowrap px-3.5 py-2.5 text-[0.8125rem] font-medium transition-colors"
                >
                    {{ $tab['label'] }}
                    @if (($tab['count'] ?? null) !== null)
                        <span class="rounded-md bg-[var(--neutral-muted)] px-1.5 py-0.5 text-[0.6875rem] font-semibold tabular-nums text-muted-foreground">{{ $tab['count'] }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{ $slot }}
</div>
