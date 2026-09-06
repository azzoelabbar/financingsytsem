@extends('layouts.guest')

@section('content')
    <x-ui.page-header :title="__('erp.auth.reset_title')" :description="__('erp.auth.reset_description')" />

    <x-auth-errors />

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.input
            id="email"
            name="email"
            type="email"
            :label="__('erp.auth.email')"
            :value="old('email', $email)"
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
            {{ __('erp.auth.reset_password') }}
        </x-ui.button>
    </form>
@endsection
