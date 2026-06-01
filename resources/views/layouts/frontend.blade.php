<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <title>@yield('title', 'Premium Directory')</title>

    @php
        $activeFavIcon = \App\Models\FavIcon::where('is_active', true)->latest()->first();
        $activeFavIconUrl = $activeFavIcon?->getPublicUrl();
    @endphp
    @if($activeFavIcon && $activeFavIconUrl !== asset('favicon.ico'))
        <link rel="icon" type="{{ $activeFavIcon->getMimeType() }}" href="{{ $activeFavIconUrl }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>

    @stack('styles')
</head>
@php
    $authProtectedRoute = collect(request()->route()?->gatherMiddleware() ?? [])->contains(
        static fn (mixed $middleware): bool => is_string($middleware)
            && ($middleware === 'provider.auth' || str_starts_with($middleware, 'auth'))
    );
@endphp

<body
    class="bg-gray-900 text-gray-100 font-sans @yield('bodyClass')"
    data-authenticated="{{ auth('web')->check() ? '1' : '0' }}"
    data-auth-protected="{{ $authProtectedRoute ? '1' : '0' }}"
    data-signin-url="{{ route('signin') }}"
    data-logout-url="{{ route('logout') }}"
    x-data="{
        mobileMenu: false,
        showScrollTop: false,
        prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        init() {
            this.showScrollTop = window.scrollY > 300;
            window.addEventListener('scroll', () => {
                this.showScrollTop = window.scrollY > 300;
            }, { passive: true });
        }
    }"
>

    @include('layouts.partials.header')

    @include('layouts.partials.global-banner')

    @include('layouts.partials.ads', ['position' => 'all_pages_top'])

    @yield('content')

    @include('layouts.partials.ads', ['position' => 'all_pages_bottom'])

    @php
        $siteSetting = \App\Models\SiteSetting::first();
    @endphp

    <script>
        window.safeStorage = window.safeStorage || {
            getLocal(key) {
                try {
                    return window.localStorage.getItem(key);
                } catch (error) {
                    return null;
                }
            },
            setLocal(key, value) {
                try {
                    window.localStorage.setItem(key, value);
                    return true;
                } catch (error) {
                    return false;
                }
            }
        };
    </script>

    @if($siteSetting && $siteSetting->enable_cookies)
        <div
            x-data="{
                show: window.safeStorage.getLocal('cookieConsent') === null,
                accept() {
                    window.safeStorage.setLocal('cookieConsent', 'accepted');
                    this.show = false;
                },
                reject() {
                    window.safeStorage.setLocal('cookieConsent', 'rejected');
                    this.show = false;
                }
            }"
            x-show="show"
            x-cloak
            class="fixed bottom-0 inset-x-0 z-50 flex justify-center items-end pb-8 pointer-events-none"
        >
            <div class="pointer-events-auto bg-gray-800 text-gray-100 rounded-xl shadow-lg px-6 py-5 flex flex-col md:flex-row items-center gap-4 max-w-xl w-full mx-4 border border-gray-700">
                <div class="flex-1 text-sm">
                    {!! nl2br(e($siteSetting->cookies_text ?? 'We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies. See our <a href=\'' . route('privacy-policy') . '\' class=\'underline text-pink-400 hover:text-pink-300\'>Privacy Policy</a>.')) !!}
                </div>
                <div class="flex gap-2 mt-3 md:mt-0">
                    <button @click="accept" class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg font-semibold transition">Accept</button>
                    <button @click="reject" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg font-semibold transition">Reject</button>
                </div>
            </div>
        </div>
    @endif

    @include('layouts.partials.footer')

    <button
        id="smooth-scroll-top"
        x-show="showScrollTop"
        x-cloak
        x-transition
        type="button"
        class="fixed bottom-6 right-6 z-40 inline-flex h-11 w-11 items-center justify-center rounded-full bg-pink-600 text-white shadow-lg transition-all duration-300 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2"
        @click="window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' })"
        aria-label="Scroll to top"
    >
        <i class="fa-solid fa-arrow-up text-sm"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/auth-session-sync.js') }}"></script>

    <script>
        function confirmSignOut(form) {
            Swal.fire({
                title: 'Sign Out?',
                text: 'Are you sure you want to sign out?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, sign out',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#db2777',
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.authSessionSync?.submitLogout(form);
                }
            });
        }
    </script>

    @if (session('auth_session_sync.type') === 'login')
        <script>
            window.authSessionSync?.notifyLogin(@json(session('auth_session_sync')));
        </script>
    @endif

    {{-- Global upload progress overlay --}}
    <div
        id="upload-progress-overlay"
        style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.75);align-items:center;justify-content:center;"
        aria-live="assertive"
        aria-label="Upload in progress"
    >
        <div style="background:#1f2937;border:1px solid #374151;border-radius:1rem;padding:2rem 2.5rem;max-width:480px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.5);">
            {{-- Spinner --}}
            <div style="display:flex;justify-content:center;margin-bottom:1.25rem;">
                <svg style="width:3rem;height:3rem;animation:upload-spin 1s linear infinite;color:#e04ecb;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </div>
            {{-- Warning message --}}
            <p style="color:#f9fafb;font-size:1rem;font-weight:700;line-height:1.6;margin:0 0 1rem;">
                ⚠️ WAIT! Do not leave or close this page.<br>
                Your photos/videos are currently being uploaded.<br>
                Please wait until this message disappears.
            </p>
            {{-- Progress bar (shown when upload:progress events are dispatched) --}}
            <div style="background:#374151;border-radius:9999px;overflow:hidden;height:0.5rem;margin-top:0.5rem;">
                <div id="upload-progress-bar" style="height:100%;width:0%;background:linear-gradient(to right,#e04ecb,#db2777);transition:width 0.3s ease;border-radius:9999px;"></div>
            </div>
            <p id="upload-progress-pct" style="color:#9ca3af;font-size:0.75rem;margin-top:0.5rem;min-height:1rem;"></p>
        </div>
    </div>
    <style>
        @keyframes upload-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
    </style>

    @stack('scripts')

    <script src="{{ asset('js/password-toggle.js') }}"></script>
    <script src="{{ asset('js/upload-overlay.js') }}"></script>
</body>
</html>
