@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    // Text shown on the trigger while nothing is picked. Falls back to the
    // first (empty-value) <option> in the slot, which is how the plain selects
    // in this app already spell their prompt.
    'placeholder' => null,
    // Below this many options the list is short enough to just read, so the
    // search box stays hidden and the control behaves like a normal dropdown.
    'searchThreshold' => 5,
    // Full-width trigger by default; pass false for toolbar-sized pickers.
    'block' => true,
    // Replace the default control styling, e.g. for the bare header pickers.
    'triggerClass' => null,
])

@php
    $pickerId = $attributes->get('id') ?: 'picker-'.Str::random(8);
    $triggerClasses = $triggerClass
        ? 'flex items-center gap-1 text-start '.$triggerClass
        : 'erp-control flex items-center justify-between gap-2 text-start'
            .($block ? '' : ' w-auto min-w-[16rem]')
            .($error ? ' erp-control-invalid' : '');
    // `required` stays off the hidden select: the browser cannot focus it to show
    // its bubble, which would silently block submit. Server-side rules still apply.
    $selectAttributes = $attributes->except(['class', 'id', 'required']);
@endphp

{{--
    Dropdown with a type-to-filter box. A real <select> stays in the DOM and
    holds the value, so wire:model, validation and plain form posts work exactly
    as they do for x-ui.select; the visible listbox only writes through to it.
--}}
<x-ui.field :label="$label" :hint="$hint" :error="$error" :required="$required" :for="$pickerId.'-trigger'" :class="$attributes->get('class')">
    <div
        x-data="mizanPicker({{ (int) $searchThreshold }})"
        x-on:keydown.escape.prevent.stop="close()"
        x-on:click.outside="close()"
        class="relative {{ $block ? '' : 'inline-block' }}"
    >
        <select
            x-ref="native"
            id="{{ $pickerId }}"
            {{ $selectAttributes }}
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
        >{{ $slot }}</select>

        <button
            type="button"
            x-ref="trigger"
            id="{{ $pickerId }}-trigger"
            x-on:click="toggle()"
            x-on:keydown.down.prevent="show()"
            x-on:keydown.enter.prevent="show()"
            :disabled="$refs.native?.disabled"
            :aria-expanded="open"
            aria-haspopup="listbox"
            @if ($error) aria-invalid="true" @endif
            class="{{ $triggerClasses }}"
        >
            <span
                class="truncate"
                :class="selectedLabel ? 'text-foreground' : 'text-muted-foreground/70'"
                x-text="selectedLabel || @js($placeholder) || placeholderOption?.text || @js(__('erp.picker.choose'))"
            >{{ $placeholder ?? __('erp.picker.choose') }}</span>
            <svg class="h-4 w-4 shrink-0 text-muted-foreground" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" /></svg>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.100ms
            class="absolute z-40 mt-1 w-full min-w-[16rem] overflow-hidden rounded-md border border-border bg-card shadow-lg"
        >
            <div x-show="showSearch" class="border-b border-border p-2">
                <input
                    type="search"
                    x-ref="search"
                    x-model="query"
                    x-on:keydown.down.prevent="move(1)"
                    x-on:keydown.up.prevent="move(-1)"
                    x-on:keydown.enter.prevent="chooseHighlighted()"
                    placeholder="{{ __('erp.picker.search') }}"
                    aria-label="{{ __('erp.picker.search') }}"
                    class="erp-control h-9"
                />
            </div>

            <ul x-ref="list" role="listbox" class="max-h-64 overflow-y-auto py-1">
                <template x-for="(option, index) in filtered" :key="option.value + '::' + index">
                    <li>
                        <button
                            type="button"
                            role="option"
                            :aria-selected="option.value === value"
                            x-on:click="choose(option.value)"
                            x-on:mousemove="highlighted = index"
                            :class="{
                                'bg-surface-sunken': index === highlighted,
                                'font-semibold text-[var(--brand-600)]': option.value === value,
                                'text-muted-foreground/70': option.value === '',
                            }"
                            class="block w-full truncate px-3 py-2 text-start text-sm text-foreground transition-colors"
                            x-text="option.text"
                        ></button>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="px-3 py-3 text-sm text-muted-foreground">
                    {{ __('erp.picker.no_results') }}
                </li>
            </ul>
        </div>
    </div>
</x-ui.field>
