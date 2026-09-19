<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Support\BrandAssets::systemName() }}</title>
        <link rel="icon" href="{{ \App\Support\BrandAssets::faviconUrl() }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/slick.css') }}"/>
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/responsive-system.css') }}" rel="stylesheet">
        <link href="{{ asset('css/chat-system.css') }}?v={{ filemtime(public_path('css/chat-system.css')) }}" rel="stylesheet">

        @php($hasViteManifest = file_exists(public_path('build/manifest.json')))
        @if($hasViteManifest)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @livewireStyles
    </head>
    <body class="userpanel-body">
        <div class="userpanel-wrap">
            @include('layouts.navigation')
            <main>
                {{ $slot }}
            </main>
        </div>

        <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
        @livewireScripts
    </body>
</html>



