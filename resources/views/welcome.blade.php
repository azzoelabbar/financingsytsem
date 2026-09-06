<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('erp.app_name') }}</title>
    @include('layouts.partials.brand-head')
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    <div class="relative flex min-h-screen flex-col overflow-hidden">
        <div class="absolute inset-0 opacity-30" style="background: radial-gradient(circle at 10% 20%, var(--teal-muted) 0%, transparent 40%), radial-gradient(circle at 90% 80%, var(--brand-200) 0%, transparent 35%);"></div>

        <header class="relative z-10 border-b border-border bg-card/80 backdrop-blur-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <x-brand.identity class="[&>img]:h-11 [&>img]:w-11" />
                <div class="flex items-center gap-2">
                    @auth
                        <x-ui.button href="{{ route('dashboard') }}" variant="primary">{{ __('erp.nav.dashboard') }}</x-ui.button>
                    @else
                        @registrationOpen
                            <x-ui.button href="{{ route('login') }}" variant="ghost">{{ __('erp.auth.login') }}</x-ui.button>
                            <x-ui.button href="{{ route('register') }}" variant="primary">{{ __('erp.auth.register') }}</x-ui.button>
                        @else
                            <x-ui.button href="{{ route('login') }}" variant="primary">{{ __('erp.auth.login') }}</x-ui.button>
                        @endregistrationOpen
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto flex max-w-6xl flex-1 flex-col justify-center px-6 py-16">
            <div>
                <div class="max-w-2xl">
                    <p class="text-sm font-medium uppercase tracking-widest text-primary">{{ __('erp.welcome.eyebrow') }}</p>
                    <h1 class="mt-4 text-4xl font-semibold leading-tight text-balance md:text-5xl">{{ __('erp.welcome.headline') }}</h1>
                    <p class="mt-6 text-lg leading-relaxed text-muted-foreground">{{ __('erp.welcome.description') }}</p>

                    <div class="mt-10 flex flex-wrap gap-3">
                        @auth
                            <x-ui.button href="{{ route('dashboard') }}" size="lg">{{ __('erp.welcome.go_dashboard') }}</x-ui.button>
                        @else
                            <x-ui.button href="{{ route('login') }}" size="lg">{{ __('erp.welcome.sign_in') }}</x-ui.button>
                            @registrationOpen
                                <x-ui.button href="{{ route('register') }}" variant="secondary" size="lg">{{ __('erp.welcome.create_account') }}</x-ui.button>
                            @endregistrationOpen
                        @endauth
                    </div>
                </div>
            </div>

            <div class="mt-16 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    'ar' => __('erp.welcome.feature_ar'),
                    'gl' => __('erp.welcome.feature_gl'),
                    'reports' => __('erp.welcome.feature_reports'),
                ] as $key => $label)
                    <div class="rounded-lg border border-border bg-card p-5">
                        <p class="font-medium">{{ $label }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ __('erp.welcome.feature_'.$key.'_hint') }}</p>
                    </div>
                @endforeach
            </div>
        </main>

        <footer class="relative z-10 border-t border-border px-6 py-4 text-center text-sm text-muted-foreground">
            {{ __('erp.welcome.footer') }}
        </footer>
    </div>
</body>
</html>
