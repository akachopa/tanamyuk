<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#2f6f4e">
        <title>{{ config('app.name', 'TanamYuk') }}</title>
        @if (file_exists(public_path('build/manifest.webmanifest')))
            <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
        @endif
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
