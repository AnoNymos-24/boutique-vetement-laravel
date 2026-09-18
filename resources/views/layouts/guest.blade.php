<!DOCTYPE html>
<html lang="fr" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $storeName }}</title>

        @if ($logoUrl = \App\Models\StoreSetting::current()->logoUrl())
            <link rel="icon" href="{{ $logoUrl }}">
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div class="auth-shell">
            <div class="auth-shell__panel" data-reveal>
                <div class="sidebar-brand" style="padding:0; border:0;">
                    <div class="sidebar-brand__mark">BB</div>
                    <span class="sidebar-brand__name">{{ $storeName }}</span>
                </div>

                <blockquote class="auth-shell__quote">
                    « Fini le cahier et les DM Instagram : le stock, les commandes et les finances au même endroit. »
                </blockquote>
            </div>

            <div class="auth-shell__form-side">
                <div class="auth-card card" data-reveal>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
