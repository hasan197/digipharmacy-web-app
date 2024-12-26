<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>DigiPharmacy</title>        
        @production
            @php
                $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            @endphp
            <link rel="stylesheet" href="/build/assets/app.css">
            <script type="module" src="/build/{{ $manifest['resources/js/app.tsx']['file'] }}" defer></script>
        @else
            @viteReactRefresh
            @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @endproduction
    </head>
    <body class="bg-background text-foreground antialiased {{ Auth::check() ? 'user-logged-in' : '' }}">
        <div id="root"></div>
    </body>
</html>
