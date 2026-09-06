@extends('layouts.guest')

@section('content')
    <x-ui.page-header :title="__('erp.auth.two_factor_title')" :description="__('erp.auth.two_factor_description')" />

    <x-auth-errors />

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-4">
        @csrf

        <x-ui.input
            id="code"
            name="code"
            type="text"
            inputmode="numeric"
            :label="__('erp.auth.two_factor_code')"
            autofocus
            autocomplete="one-time-code"
            :error="$errors->first('code')"
        />

        <x-ui.button type="submit" class="w-full">
            {{ __('erp.auth.confirm') }}
        </x-ui.button>
    </form>

    <details class="mt-6">
        <summary class="cursor-pointer text-sm text-muted-foreground hover:text-foreground">{{ __('erp.auth.use_recovery_code') }}</summary>
        <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-4 space-y-4">
            @csrf
            <x-ui.input
                id="recovery_code"
                name="recovery_code"
                type="text"
                :label="__('erp.auth.recovery_code')"
                autocomplete="one-time-code"
                :error="$errors->first('recovery_code')"
            />
            <x-ui.button type="submit" variant="secondary" class="w-full">
                {{ __('erp.auth.confirm') }}
            </x-ui.button>
        </form>
    </details>
@endsection
