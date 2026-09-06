@extends('layouts.guest')

@section('content')
    <x-ui.page-header :title="__('erp.auth.verify_title')" :description="__('erp.auth.verify_description')" />

    @if ($status === 'verification-link-sent')
        <x-ui.alert variant="success" class="mb-4">{{ __('erp.auth.verification_sent') }}</x-ui.alert>
    @endif

    <x-auth-errors />

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
        @csrf
        <x-ui.button type="submit" class="w-full">
            {{ __('erp.auth.resend_verification') }}
        </x-ui.button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <x-ui.button type="submit" variant="ghost" class="w-full">
            {{ __('erp.auth.logout') }}
        </x-ui.button>
    </form>
@endsection
