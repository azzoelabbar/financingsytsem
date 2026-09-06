@extends('layouts.erp')

@section('content')
    @include('settings.partials.nav')

    <x-ui.page-header :title="__('erp.settings.security')" :description="__('erp.settings.security_description')" />

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card class="max-w-xl" :title="__('erp.settings.change_password')">
        <form method="POST" action="{{ route('user-password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <x-ui.input
                id="current_password"
                name="current_password"
                type="password"
                :label="__('erp.settings.current_password')"
                required
                autocomplete="current-password"
                :error="$errors->first('current_password')"
            />

            <x-ui.input
                id="password"
                name="password"
                type="password"
                :label="__('erp.auth.password')"
                required
                autocomplete="new-password"
                :error="$errors->first('password')"
            />

            <x-ui.input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                :label="__('erp.auth.password_confirmation')"
                required
                autocomplete="new-password"
            />

            <div class="flex justify-end">
                <x-ui.button type="submit">{{ __('erp.save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($canManageTwoFactor ?? false)
        <x-ui.card class="mt-6 max-w-xl" :title="__('erp.settings.two_factor')">
            <p class="mb-4 text-sm text-muted-foreground">{{ __('erp.settings.two_factor_description') }}</p>

            @if ($twoFactorEnabled ?? false)
                <x-ui.badge variant="success">{{ __('erp.settings.two_factor_enabled') }}</x-ui.badge>
                <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger" size="sm">{{ __('erp.settings.disable_two_factor') }}</x-ui.button>
                </form>
            @else
                <x-ui.badge variant="outline">{{ __('erp.settings.two_factor_disabled') }}</x-ui.badge>
                <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" size="sm">{{ __('erp.settings.enable_two_factor') }}</x-ui.button>
                </form>
            @endif
        </x-ui.card>
    @endif
@endsection
