@props([
    'label',
    // Livewire action to call once confirmed.
    'action',
    'title' => null,
    // Spell out the consequence: posting is irreversible, deletion is permanent.
    'message' => null,
    'confirmLabel' => null,
    'variant' => 'primary',
    'size' => 'md',
])

{{--
    Consequence confirmation for irreversible accounting actions (posting,
    locking, disposing). The dialog states what will happen before it happens.
--}}
<div x-data="{ open: false }" class="inline-flex">
    <x-ui.button :variant="$variant" :size="$size" x-on:click="open = true" {{ $attributes }}>
        {{ $label }}
    </x-ui.button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[60] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            x-on:keydown.escape.window="open = false"
        >
            <div class="absolute inset-0 bg-[var(--ink)]/50" x-on:click="open = false" x-transition.opacity></div>

            <div
                class="relative w-full max-w-md overflow-hidden rounded-lg border border-border bg-card shadow-elevation-lg"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-start gap-3 px-5 py-4">
                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--warning-muted)] text-[var(--warning-color)]">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3.6a1.7 1.7 0 013 0l6.3 11.2A1.7 1.7 0 0116.3 17H3.7a1.7 1.7 0 01-1.5-2.2L8.5 3.6zM10 7a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1zm0 7.5a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-[0.9375rem] font-semibold text-foreground">{{ $title ?? $label }}</h2>
                        @if ($message)
                            <p class="mt-1.5 text-[0.8125rem] leading-relaxed text-muted-foreground">{{ $message }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-border bg-surface-sunken px-5 py-3">
                    <x-ui.button variant="secondary" size="sm" x-on:click="open = false">{{ __('erp.cancel') }}</x-ui.button>
                    <x-ui.button :variant="$variant" size="sm" wire:click="{{ $action }}" x-on:click="open = false" wire:loading.attr="disabled">
                        {{ $confirmLabel ?? $label }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </template>
</div>
