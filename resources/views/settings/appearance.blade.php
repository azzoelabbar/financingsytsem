@extends('layouts.erp')

@section('content')
    @include('settings.partials.nav')

    <x-ui.page-header :title="__('erp.settings.appearance')" :description="__('erp.settings.appearance_description')" />

    <x-ui.card class="max-w-xl" :title="__('erp.settings.language')">
        <p class="mb-4 text-sm text-muted-foreground">{{ __('erp.settings.language_description') }}</p>

        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('locale.switch', 'ar') }}">
                @csrf
                <x-ui.button type="submit" :variant="app()->getLocale() === 'ar' ? 'primary' : 'secondary'">العربية</x-ui.button>
            </form>
            <form method="POST" action="{{ route('locale.switch', 'en') }}">
                @csrf
                <x-ui.button type="submit" :variant="app()->getLocale() === 'en' ? 'primary' : 'secondary'">English</x-ui.button>
            </form>
        </div>
    </x-ui.card>

    <x-ui.card class="mt-6 max-w-xl" :title="__('erp.settings.theme')">
        <p class="text-sm text-muted-foreground">{{ __('erp.settings.theme_description') }}</p>
        <x-ui.badge variant="primary" class="mt-3">{{ __('erp.settings.theme_light') }}</x-ui.badge>
    </x-ui.card>
@endsection
