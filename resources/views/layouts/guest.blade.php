<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ request()->attributes->get('dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ __('erp.app_name') }}</title>
    @include('layouts.partials.brand-head')
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    <div class="flex min-h-screen">
        <div class="relative hidden w-1/2 overflow-hidden bg-[var(--ink)] lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 80%, var(--teal) 0%, transparent 50%), radial-gradient(circle at 80% 20%, var(--brand-200) 0%, transparent 40%);"></div>
            <div class="relative z-10 p-12">
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-semibold leading-tight text-white">{{ __('erp.app_name') }}</span>
                    <span class="text-sm leading-tight text-white/65">{{ __('erp.app_tagline') }}</span>
                </div>
                <p class="mt-7 max-w-md text-base leading-relaxed text-white/75">{{ __('erp.auth.hero_description') }}</p>
            </div>
            <div class="relative z-10 border-t border-white/10 p-12">
                <p class="text-sm text-white/50">{{ __('erp.auth.hero_footer') }}</p>
            </div>
        </div>

        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <x-brand.identity class="[&>img]:h-12 [&>img]:w-12" />
                </div>
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
