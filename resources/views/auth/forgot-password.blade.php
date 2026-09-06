@extends('layouts.guest')

@section('content')
    <x-ui.page-header :title="__('erp.auth.forgot_title')" :description="__('erp.auth.forgot_description')" />

    @if ($status)
        <x-ui.alert variant="success" class="mb-4">{{ $status }}</x-ui.alert>
    @endif

    <x-auth-errors />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

        <x-ui.button type="submit" class="w-full">
            {{ __('erp.auth.send_reset_link') }}
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-muted-foreground">
        <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">{{ __('erp.auth.back_to_login') }}</a>
    </p>
@endsection
