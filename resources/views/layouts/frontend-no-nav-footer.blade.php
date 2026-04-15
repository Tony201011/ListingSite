<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Premium Directory')</title>

    @php
        $activeFavIcon = \App\Models\FavIcon::where('is_active', true)->latest()->first();
    @endphp
    @if($activeFavIcon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::disk('public')->url($activeFavIcon->icon_path) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(180deg, rgba(17,24,39,0.7) 0%, rgba(17,24,39,1) 100%); }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-900 text-gray-100 font-sans">
    @yield('content')
    @stack('scripts')
</body>
</html>
