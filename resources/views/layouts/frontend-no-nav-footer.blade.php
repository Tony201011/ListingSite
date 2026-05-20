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
        <link rel="icon" type="{{ $activeFavIcon->getMimeType() }}" href="{{ '/storage/' . $activeFavIcon->icon_path }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-900 text-gray-100 font-sans">
    @yield('content')
    @stack('scripts')
</body>
</html>
