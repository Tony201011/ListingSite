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
    @stack('head')
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

    $routeName = request()->route()?->getName();
    $providerProfileRouteNames = [
        'select-profile',
        'my-listings',
        'my-listings.profile.show',
        'my-listings.profile.gallery',
        'my-listings.show',
        'my-listings.feature',
        'my-profile',
        'activity-logs',
        'profile-spending-history',
        'edit-profile',
        'edit-profile.save',
        'add-photos',
        'photos.index',
        'photos',
        'photos.upload',
        'editor.upload-image',
        'photos.setCover',
        'photos.destroy',
        'availability.edit',
        'availability.update',
        'availability.show',
        'featured',
        'featured.purchase',
        'upload-video',
        'my-videos',
        'videos.upload',
        'videos.destroy',
        'my-tours',
        'my-tours.store',
        'my-tours.update',
        'my-tours.toggle',
        'my-tours.destroy',
        'search-cities',
        'verify.photos',
        'photo-verification.upload',
        'photo-verification.delete-photo',
        'short-url',
        'short-url.update',
        'referral',
        'online-now',
        'online.update-status',
        'available-now',
        'available.update-status',
        'set-and-forget',
        'set-and-forget.save',
        'my-babe-rank',
        'babe-rank-read-more',
        'profile-message',
        'profile-message.store',
        'hide-show-profile',
        'update-hide-show-profile',
        'status',
        'profile-setting',
    ];

    $isProviderProfileRoute = is_string($routeName)
        && (str_starts_with($routeName, 'profiles.')
            || str_starts_with($routeName, 'my-rate.')
            || in_array($routeName, $providerProfileRouteNames, true));
@endphp

<body
    class="flex min-h-screen flex-col bg-gray-900 text-gray-100 font-sans @yield('bodyClass')"
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

    <main class="flex-1">
        @include('layouts.partials.global-banner')

        @include('layouts.partials.ads', ['position' => 'all_pages_top'])

        @if($isProviderProfileRoute)
            <div class="provider-page-shell min-h-screen bg-gray-50">
                <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8">
                        <div class="provider-page-shell-content">
                            @yield('content')
                        </div>
                    </div>
                </main>
            </div>
        @else
            @yield('content')
        @endif

        @include('layouts.partials.ads', ['position' => 'all_pages_bottom'])
    </main>

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
                show: window.safeStorage.getLocal('ageVerified') !== 'yes',
                init() {
                    this.$watch('show', (isVisible) => {
                        document.body.classList.toggle('overflow-hidden', isVisible);
                    });
                    document.body.classList.toggle('overflow-hidden', this.show);
                },
                enter() {
                    window.safeStorage.setLocal('ageVerified', 'yes');
                    this.show = false;
                }
            }"
            x-show="show"
            x-cloak
            class="fixed inset-0 z-[80] flex items-center justify-center bg-gray-950/75 backdrop-blur-sm px-4 py-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="age-warning-title"
        >
            <div class="w-full max-w-md rounded-2xl border border-pink-400/30 bg-gray-900 p-6 text-center text-gray-100 shadow-2xl sm:p-8">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-2 border-pink-400 bg-pink-500/20 text-pink-300">
                    <span class="text-lg font-extrabold tracking-wide">18+</span>
                </div>

                <h2 id="age-warning-title" class="text-xl font-bold text-white sm:text-2xl">
                    Adults Only (18+)
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-gray-300 sm:text-base">
                    This website contains adult content and is intended only for individuals who are 18 years of age or older.
                    By entering, you confirm you meet this requirement.
                </p>

                <button
                    @click="enter"
                    type="button"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-pink-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 focus:ring-offset-gray-900 sm:text-base"
                >
                    I am 18+ years old / Enter
                </button>

                <a
                    href="https://www.google.com"
                    class="mt-4 inline-block text-sm font-medium text-gray-400 underline transition hover:text-gray-200"
                >
                    Exit
                </a>
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
