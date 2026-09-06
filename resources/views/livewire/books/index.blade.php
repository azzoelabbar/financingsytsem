<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.books')]]" :title="__('erp.nav.books')" :description="__('erp.books.description')" />

    @if ($books === [] || (is_countable($books) && count($books) === 0))
        @include('livewire.partials.empty-state')
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($books as $book)
                @php
                    $code = strtolower($book->code ?? '');
                    $tone = match ($book->code) {
                        'IFRS' => 'info',
                        'TAX' => 'warning',
                        default => 'primary',
                    };
                    $toneColor = match ($book->code) {
                        'IFRS' => 'var(--info-color)',
                        'TAX' => 'var(--gold)',
                        default => 'var(--brand-600)',
                    };
                    $meaning = __('erp.book_info.'.$code.'.meaning');
                    $note = __('erp.book_info.'.$code.'.note');
                    $differences = __('erp.book_info.'.$code.'.differences');
                    $differences = is_array($differences) ? $differences : [];
                    $hasInfo = ! str_contains($meaning, 'book_info');
                    $isCurrent = $currentBook?->id === $book->id;
                    $bookName = app()->getLocale() === 'ar' ? ($book->name_ar ?? $book->code) : ($book->name_en ?? $book->name_ar ?? $book->code);
                @endphp
                <div @class([
                    'relative flex flex-col overflow-hidden rounded-lg border bg-card transition-colors',
                    'border-[color:var(--brand-600)] ring-1 ring-[color:var(--brand-600)]/20' => $isCurrent,
                    'border-border' => ! $isCurrent,
                ])>
                    <span class="absolute inset-y-0 start-0 w-0.5" style="background: {{ $toneColor }}" aria-hidden="true"></span>

                    <div class="flex-1 p-5">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg text-white" style="background: {{ $toneColor }}">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                                </span>
                                <div>
                                    <p class="text-base font-semibold leading-tight text-foreground">{{ $book->code }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $bookName }}</p>
                                </div>
                            </div>
                            @if ($isCurrent)
                                <x-ui.badge variant="primary">{{ __('erp.books.current') }}</x-ui.badge>
                            @endif
                        </div>

                        @if ($hasInfo)
                            <p class="text-sm font-medium text-foreground">{{ $meaning }}</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">{{ $note }}</p>

                            @if ($differences !== [])
                                <div class="mt-4 border-t border-border pt-3">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ __('erp.books.differences_title') }}</p>
                                    <ul class="space-y-1.5">
                                        @foreach ($differences as $diff)
                                            <li class="flex gap-2 text-sm leading-relaxed text-foreground/90">
                                                <svg class="mt-1 h-3.5 w-3.5 shrink-0" style="color: {{ $toneColor }}" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" /></svg>
                                                <span>{{ $diff }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @else
                            <p class="text-sm leading-relaxed text-muted-foreground">{{ $bookName }}</p>
                        @endif
                    </div>

                    <div class="flex items-center justify-between border-t border-border bg-surface-sunken px-5 py-3">
                        <span class="flex items-center gap-1.5 text-xs font-medium {{ $book->is_active ? 'text-[var(--success-color)]' : 'text-muted-foreground' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $book->is_active ? 'bg-[var(--success-color)]' : 'bg-muted-foreground' }}"></span>
                            {{ $book->is_active ? __('erp.books.active') : __('erp.books.inactive') }}
                        </span>
                        @if ($isCurrent)
                            <span class="text-xs text-muted-foreground">{{ __('erp.books.selected') }}</span>
                        @elseif ($book->is_active)
                            <x-ui.button size="sm" variant="secondary" wire:click="setBook({{ $book->id }})">{{ __('erp.books.use_book') }}</x-ui.button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-xs text-muted-foreground">{{ __('erp.books.footnote') }}</p>
    @endif
</div>
