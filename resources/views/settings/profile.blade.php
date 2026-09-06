@extends('layouts.erp')

@section('content')
    @include('settings.partials.nav')

    <x-ui.page-header :title="__('erp.settings.profile')" :description="__('erp.settings.profile_description')" />

    @if ($status)
        <x-ui.alert variant="success" class="mb-4">{{ $status }}</x-ui.alert>
    @endif

    @if ($mustVerifyEmail && ! $user->hasVerifiedEmail())
        <x-ui.alert variant="warning" class="mb-4">
            {{ __('erp.auth.verify_notice') }}
            <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                @csrf
                <x-ui.button type="submit" variant="secondary" size="sm">{{ __('erp.auth.resend_verification') }}</x-ui.button>
            </form>
        </x-ui.alert>
    @endif

    <x-ui.card class="max-w-xl">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <x-ui.input
                id="name"
                name="name"
                type="text"
                :label="__('erp.auth.name')"
                :value="old('name', $user->name)"
                required
                :error="$errors->first('name')"
            />

            <x-ui.input
                id="email"
                name="email"
                type="email"
                :label="__('erp.auth.email')"
                :value="old('email', $user->email)"
                required
                :error="$errors->first('email')"
            />

            <div class="flex justify-end">
                <x-ui.button type="submit">{{ __('erp.save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
