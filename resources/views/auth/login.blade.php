@extends('layouts.guest')

@section('content')
    <x-ui.page-header :title="__('erp.auth.login_title')" :description="__('erp.auth.login_description')" />

    @if ($status)
        <x-ui.alert variant="success" class="mb-4">{{ $status }}</x-ui.alert>
    @endif

    <x-auth-errors />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-ui.input
            id="email"
            name="email"
            type="email"
            :label="__('erp.auth.email')"
            :value="old('email')"
            required
            autofocus
            autocomplete="username"
            :error="$errors->first('email')"
        />

        <x-ui.input
            id="password"
            name="password"
            type="password"
            :label="__('erp.auth.password')"
            required
            autocomplete="current-password"
            :error="$errors->first('password')"
        />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-muted-foreground">
                <input type="checkbox" name="remember" class="rounded border-input text-primary focus:ring-ring">
                {{ __('erp.auth.remember') }}
            </label>

            @if ($canResetPassword)
                <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                    {{ __('erp.auth.forgot_password') }}
                </a>
            @endif
        </div>

        <x-ui.button type="submit" class="w-full">
            {{ __('erp.auth.login') }}
        </x-ui.button>
    </form>

    @registrationOpen
        <p class="mt-6 text-center text-sm text-muted-foreground">
            {{ __('erp.auth.no_account') }}
            <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">{{ __('erp.auth.register') }}</a>
        </p>
    @endregistrationOpen
@endsection
