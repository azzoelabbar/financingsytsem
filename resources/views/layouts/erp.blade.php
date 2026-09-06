<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ request()->attributes->get('dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}" class="overflow-x-clip">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ __('erp.app_name') }}</title>
    @include('layouts.partials.brand-head')
    @fonts
    @vite(['resources/css/app.css'])
    <script src="{{ Vite::asset('resources/js/app.js') }}" data-navigate-once></script>
    @livewireStyles
</head>
<body class="min-h-screen overflow-x-clip bg-background font-sans text-foreground antialiased">
    <x-ui.flash />
    <div
        x-data="mizanShell"
        class="flex min-h-screen"
        @keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $refs.menuToggle.focus() } userMenuOpen = false"
        @resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
        @keydown.window="if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); openCommands() }"
    >
        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-[var(--ink)]/60 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
            x-cloak
        ></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0 visible' : 'invisible lg:visible -translate-x-full rtl:translate-x-full'"
            class="mizan-sidebar fixed inset-y-0 start-0 z-50 flex w-[17rem] flex-col border-e border-sidebar-border bg-sidebar transition-transform duration-200 lg:z-auto lg:translate-x-0 rtl:lg:translate-x-0"
            @keydown.tab="
                if (sidebarOpen) {
                    const items = [...$el.querySelectorAll('a[href], button:not([disabled])')].filter(item => item.getClientRects().length);
                    const first = items[0], last = items[items.length - 1];
                    if ($event.shiftKey && $event.target === first) { $event.preventDefault(); last.focus() }
                    if (!$event.shiftKey && $event.target === last) { $event.preventDefault(); first.focus() }
                }
            "
        >
            {{-- Brand --}}
            <div class="flex items-center justify-between border-b border-sidebar-border px-5 py-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5" wire:navigate>
                    <x-brand.identity inverted />
                </a>
                <button x-ref="closeMenu" type="button" class="rounded-md p-1.5 text-sidebar-foreground hover:bg-white/10 lg:hidden" @click="sidebarOpen = false; $refs.menuToggle.focus()" aria-label="{{ __('erp.close_menu') }}">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-4 pb-1 pt-4">
                <button type="button" @click="openCommands()" class="flex w-full items-center gap-2 rounded-lg border border-sidebar-border bg-white/5 px-3 py-2.5 text-xs text-sidebar-foreground hover:bg-white/10">
                    <x-ui.icon name="search" class="h-4 w-4" />
                    <span>{{ __('erp.mizan.navigate') }}</span>
                    <kbd class="ms-auto rounded border border-white/15 px-1.5 text-[10px] text-sidebar-muted" dir="ltr">⌘ K</kbd>
                </button>
            </div>
            <nav data-command-source class="min-h-0 flex-1 space-y-0.5 overflow-y-auto px-3 pb-4 pt-2">
                @include('layouts.partials.erp-nav')
            </nav>

            <div class="border-t border-sidebar-border p-2">
                <a href="{{ route('profile.edit') }}" class="erp-sidebar-link" wire:navigate>
                    <svg class="h-4 w-4 shrink-0 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    {{ __('erp.nav.settings') }}
                </a>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col" :inert="sidebarOpen">
            <header class="mizan-topbar sticky top-0 z-30 border-b border-border/70">
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 md:px-6">
                    <div class="mizan-context-group flex min-w-0 items-center gap-3">
                        <button x-ref="menuToggle" type="button" class="shrink-0 rounded-md border border-border p-2 text-muted-foreground hover:bg-muted lg:hidden" @click="sidebarOpen = true; $nextTick(() => $refs.closeMenu.focus())" aria-label="{{ __('erp.open_menu') }}" :aria-expanded="sidebarOpen">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        </button>
                        <livewire:layout.context-bar />
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <button type="button" @click="openCommands()" class="mizan-command-trigger" aria-label="{{ __('erp.mizan.navigate') }}">
                            <x-ui.icon name="search" class="h-4 w-4" />
                            <span class="hidden 2xl:inline">{{ __('erp.mizan.navigate') }}</span>
                        </button>
                        {{-- Locale segmented control --}}
                        <div class="flex items-center rounded-lg border border-border bg-background p-0.5">
                            <form method="POST" action="{{ route('locale.switch', 'ar') }}">
                                @csrf
                                <button type="submit" @class([
                                    'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                                    'bg-primary text-primary-foreground shadow-sm' => app()->getLocale() === 'ar',
                                    'text-muted-foreground hover:text-foreground' => app()->getLocale() !== 'ar',
                                ])>ع</button>
                            </form>
                            <form method="POST" action="{{ route('locale.switch', 'en') }}">
                                @csrf
                                <button type="submit" @class([
                                    'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                                    'bg-primary text-primary-foreground shadow-sm' => app()->getLocale() === 'en',
                                    'text-muted-foreground hover:text-foreground' => app()->getLocale() !== 'en',
                                ])>EN</button>
                            </form>
                        </div>

                        {{-- User menu --}}
                        <div class="relative">
                            <button
                                type="button"
                                @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2 rounded-lg border border-border bg-background px-2 py-1.5 hover:bg-muted"
                            >
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--teal)] text-xs font-semibold text-white">
                                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </span>
                                <span class="hidden max-w-[10rem] truncate font-medium sm:inline">{{ auth()->user()->name }}</span>
                                <svg class="h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div
                                x-show="userMenuOpen"
                                @click.outside="userMenuOpen = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute end-0 z-50 mt-2 w-52 overflow-hidden rounded-xl border border-border bg-popover py-1 shadow-elevation-lg"
                                x-cloak
                            >
                                <div class="border-b border-border px-4 py-2.5">
                                    <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-muted-foreground">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-muted" wire:navigate>{{ __('erp.nav.profile') }}</a>
                                <a href="{{ route('security.edit') }}" class="block px-4 py-2 text-sm hover:bg-muted" wire:navigate>{{ __('erp.nav.security') }}</a>
                                <div class="my-1 border-t border-border"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-start text-sm text-destructive hover:bg-muted">
                                        {{ __('erp.auth.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mizan-content min-w-0 flex-1 px-4 py-6 md:px-6 lg:px-7 lg:py-8">
                <div class="mx-auto max-w-[1600px]">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>
        </div>
        @include('layouts.partials.command-palette')
    </div>

    @livewireScripts
</body>
</html>
