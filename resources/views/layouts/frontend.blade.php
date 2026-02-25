<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $slug = trim(request()->path(), '/');
        if ($slug === '') {
            $slug = 'home';
        }
        $metaDescription = get_meta_description_for_slug($slug);
        $metaKeywords = get_meta_keywords_for_slug($slug);
    @endphp
    <meta name="description" content="{{ $metaDescription ?? '' }}">
    <meta name="keywords" content="{{ $metaKeywords ?? '' }}">
    <title>@yield('title', 'Premium Directory')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(180deg, rgba(17,24,39,0.7) 0%, rgba(17,24,39,1) 100%); }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-900 text-gray-100 font-sans" x-data="{ mobileMenu: false, loginModal: false, registerModal: false }">

    @include('layouts.partials.header')

    @yield('content')

    {{-- Cookie Consent Banner (dynamic) --}}
    @php
        $siteSetting = \App\Models\SiteSetting::first();
    @endphp
    @if($siteSetting && $siteSetting->enable_cookies)
        <div
            x-data="{
                show: localStorage.getItem('cookieConsent') === null,
                accept() {
                    localStorage.setItem('cookieConsent', 'accepted');
                    this.show = false;
                },
                reject() {
                    localStorage.setItem('cookieConsent', 'rejected');
                    this.show = false;
                }
            }"
            x-show="show"
            x-cloak
            class="fixed bottom-0 inset-x-0 z-50 flex justify-center items-end pb-8 pointer-events-none"
        >
            <div class="pointer-events-auto bg-gray-800 text-gray-100 rounded-xl shadow-lg px-6 py-5 flex flex-col md:flex-row items-center gap-4 max-w-xl w-full mx-4 border border-gray-700">
                <div class="flex-1 text-sm">
                    {!! nl2br(e($siteSetting->cookies_text ?? 'We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies. See our <a href=\'' . route('privacy-policy') . '\' class=\'underline text-purple-400 hover:text-pink-400\'>Privacy Policy</a>.')) !!}
                </div>
                <div class="flex gap-2 mt-3 md:mt-0">
                    <button @click="accept" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition">Accept</button>
                    <button @click="reject" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg font-semibold transition">Reject</button>
                </div>
            </div>
        </div>
    @endif

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
