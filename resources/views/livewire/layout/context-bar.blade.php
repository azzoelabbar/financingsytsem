@php
    $bookTone = match ($book?->code) {
        'IFRS' => 'var(--info-color)',
        'TAX' => 'var(--gold)',
        default => 'var(--brand-600)',
    };
@endphp

<div
    class="mizan-context-bar flex min-w-0 max-w-full items-stretch rounded-md border border-border bg-card text-sm"
    x-on:accounting-context-changed.window="window.location.reload()"
>
    {{-- Company --}}
    <div class="mizan-company-context group relative flex min-w-0 items-center gap-2 border-e border-border px-3 py-1.5">
        <svg class="h-4 w-4 shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
        <div class="min-w-0 flex flex-col leading-none">
            <span class="text-[0.625rem] font-medium uppercase tracking-wide text-muted-foreground">{{ __('erp.company') }}</span>
            <select
                wire:change="setCompany($event.target.value)"
                aria-label="{{ __('erp.company') }}"
                class="-ms-0.5 mt-0.5 max-w-[10rem] cursor-pointer truncate border-0 bg-transparent p-0 pe-4 text-[0.8125rem] font-semibold text-foreground focus:outline-none focus:ring-0"
            >
                @forelse ($companies as $c)
                    <option value="{{ $c->id }}" @selected($company?->id === $c->id)>{{ $c->code }} - {{ app()->getLocale() === 'ar' ? ($c->name_ar ?? $c->code) : ($c->name_en ?? $c->name_ar ?? $c->code) }}</option>
                @empty
                    <option value="">{{ __('erp.no_company') }}</option>
                @endforelse
            </select>
        </div>
    </div>

    {{-- Book --}}
    <div class="relative flex shrink-0 items-center gap-2 border-e border-border px-3 py-1.5" x-data="{ bookInfo: false }">
        <span class="h-6 w-1 shrink-0 rounded-full" style="background: {{ $bookTone }}" aria-hidden="true"></span>
        <div class="flex flex-col leading-none">
            <span class="text-[0.625rem] font-medium uppercase tracking-wide text-muted-foreground">{{ __('erp.book') }}</span>
            <select
                wire:change="setBook($event.target.value)"
                aria-label="{{ __('erp.book') }}"
                class="-ms-0.5 mt-0.5 cursor-pointer border-0 bg-transparent p-0 pe-4 text-[0.8125rem] font-semibold text-foreground focus:outline-none focus:ring-0"
            >
                @forelse ($books as $b)
                    <option value="{{ $b->id }}" @selected($book?->id === $b->id)>{{ $b->code }}</option>
                @empty
                    <option value="">{{ __('erp.no_book') }}</option>
                @endforelse
            </select>
        </div>

        {{-- Basis info --}}
        <button type="button" @click.stop="bookInfo = !bookInfo" @keydown.escape.window="bookInfo = false" class="shrink-0 text-muted-foreground/70 transition-colors hover:text-[var(--brand-600)]" aria-label="{{ __('erp.books.bases_title') }}">
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM11 9a1 1 0 10-2 0v4a1 1 0 102 0V9zm-1-4.5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" /></svg>
        </button>

        <div
            x-show="bookInfo"
            x-cloak
            @click.outside="bookInfo = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="absolute end-0 top-full z-50 mt-2 w-[min(20rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-border bg-popover text-start shadow-elevation-lg sm:start-0 sm:end-auto"
        >
            <div class="border-b border-border bg-surface-sunken px-4 py-2.5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ __('erp.books.bases_title') }}</p>
            </div>
            <ul class="divide-y divide-border">
                @foreach ([['local', 'var(--brand-600)'], ['ifrs', 'var(--info-color)'], ['tax', 'var(--gold)']] as [$code, $color])
                    <li class="flex gap-3 px-4 py-3">
                        <span class="mt-1 h-4 w-1 shrink-0 rounded-full" style="background: {{ $color }}"></span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-foreground">{{ strtoupper($code) }} - {{ __('erp.book_info.'.$code.'.meaning') }}</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-muted-foreground">{{ __('erp.book_info.'.$code.'.note') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('books.index') }}" wire:navigate class="block border-t border-border bg-surface-sunken px-4 py-2.5 text-xs font-medium text-[var(--brand-600)] hover:underline">{{ __('erp.view_all') }}</a>
        </div>
    </div>

    {{-- Period (read-only) --}}
    @if ($period)
        <div class="hidden items-center gap-2 px-3 py-1.5 sm:flex">
            <svg class="h-4 w-4 shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
            <div class="flex flex-col leading-none">
                <span class="text-[0.625rem] font-medium uppercase tracking-wide text-muted-foreground">{{ __('erp.period') }}</span>
                <span class="mt-0.5 text-[0.8125rem] font-semibold text-foreground" dir="ltr">{{ $period->start_date?->format('M Y') }}</span>
            </div>
        </div>
    @endif
</div>
