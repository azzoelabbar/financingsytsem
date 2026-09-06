<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="overflow-x-clip">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('erp.onboarding.title') }} - {{ __('erp.app_name') }}</title>
    @include('layouts.partials.brand-head')
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    @php
        $steps = [
            ['key' => 'company', 'route' => 'onboarding.company', 'label' => __('erp.onboarding.step_company')],
            ['key' => 'accounting', 'route' => 'onboarding.accounting', 'label' => __('erp.onboarding.step_accounting')],
            ['key' => 'ready', 'route' => null, 'label' => __('erp.onboarding.step_ready')],
        ];
        $current = request()->routeIs('onboarding.accounting') ? 1 : 0;
    @endphp

    <div class="flex min-h-screen flex-col">
        {{-- Top bar --}}
        <header class="flex items-center justify-between border-b border-border px-4 py-3 md:px-8">
            <x-brand.identity :show-tagline="false" />
            <div class="flex items-center gap-2">
                <div class="flex items-center rounded-lg border border-border bg-background p-0.5">
                    <form method="POST" action="{{ route('locale.switch', 'ar') }}">
                        @csrf
                        <button type="submit" @class(['rounded-md px-2.5 py-1 text-xs font-medium transition-colors', 'bg-primary text-primary-foreground' => app()->getLocale() === 'ar', 'text-muted-foreground hover:text-foreground' => app()->getLocale() !== 'ar'])>ع</button>
                    </form>
                    <form method="POST" action="{{ route('locale.switch', 'en') }}">
                        @csrf
                        <button type="submit" @class(['rounded-md px-2.5 py-1 text-xs font-medium transition-colors', 'bg-primary text-primary-foreground' => app()->getLocale() === 'en', 'text-muted-foreground hover:text-foreground' => app()->getLocale() !== 'en'])>EN</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md px-3 py-1.5 text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground">{{ __('erp.auth.logout') }}</button>
                </form>
            </div>
        </header>

        <main class="flex flex-1 items-start justify-center px-4 py-8 md:py-14">
            <div class="w-full max-w-xl">
                {{-- Progress --}}
                <ol class="mb-8 flex items-center justify-center gap-2 sm:gap-4">
                    @foreach ($steps as $i => $step)
                        @php $state = $i < $current ? 'done' : ($i === $current ? 'active' : 'upcoming'); @endphp
                        <li class="flex items-center gap-2">
                            <span @class([
                                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold',
                                'bg-[var(--ink)] text-white' => $state === 'active',
                                'bg-[var(--success-muted)] text-[var(--success-color)]' => $state === 'done',
                                'border border-border-strong text-muted-foreground' => $state === 'upcoming',
                            ])>
                                @if ($state === 'done')
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>
                            <span @class(['hidden text-sm sm:inline', 'font-medium text-foreground' => $state !== 'upcoming', 'text-muted-foreground' => $state === 'upcoming'])>{{ $step['label'] }}</span>
                            @if (! $loop->last)
                                <span class="mx-1 h-px w-6 bg-border sm:w-8"></span>
                            @endif
                        </li>
                    @endforeach
                </ol>

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
