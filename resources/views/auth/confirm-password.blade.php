@extends('layouts.guest')

@section('content')
    <x-ui.page-header :title="__('erp.auth.confirm_password_title')" :description="__('erp.auth.confirm_password_description')" />

    <x-auth-errors />

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <x-ui.input
            id="password"
            name="password"
            type="password"
            :label="__('erp.auth.password')"
            required
            autofocus
            autocomplete="current-password"
            :error="$errors->first('password')"
        />

        <x-ui.button type="submit" class="w-full">
            {{ __('erp.auth.confirm') }}
        </x-ui.button>
    </form>
@endsection
