@extends('layouts.guest')

@section('content')
    <x-ui.page-header :title="__('erp.auth.register_title')" :description="__('erp.auth.register_description')" />

    <x-auth-errors />

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <x-ui.input
            id="name"
            name="name"
            type="text"
            :label="__('erp.auth.name')"
            :value="old('name')"
            required
            autofocus
            autocomplete="name"
            :error="$errors->first('name')"
        />

        <x-ui.input
            id="email"
            name="email"
            type="email"
            :label="__('erp.auth.email')"
            :value="old('email')"
            required
            autocomplete="username"
            :error="$errors->first('email')"
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

        <x-ui.button type="submit" class="w-full">
            {{ __('erp.auth.register') }}
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-muted-foreground">
        {{ __('erp.auth.have_account') }}
        <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">{{ __('erp.auth.login') }}</a>
    </p>
@endsection
