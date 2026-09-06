<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url={{ auth()->check() ? route('dashboard') : route('home') }}">
    <title>{{ config('app.name') }}</title>
</head>
<body>
    <p><a href="{{ auth()->check() ? route('dashboard') : route('home') }}">{{ __('erp.continue') }}</a></p>
</body>
</html>
