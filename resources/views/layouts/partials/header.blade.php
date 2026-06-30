@php
    $logoType = $headerWidget?->logo_type ?: 'text';
    $logoPath = $headerWidget?->logo_path;
    $logoUrl = filled($logoPath) ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;
    $logoMaxWidth = max((int) ($headerWidget?->logo_max_width ?? 160), 20);
    $logoMaxHeight = max((int) ($headerWidget?->logo_max_height ?? 40), 20);
    $logoStyle = "max-width: {$logoMaxWidth}px; max-height: {$logoMaxHeight}px;";
    $headerBackgroundColor = trim((string) ($headerWidget?->header_background_color ?? ''));
    $headerHeight = max((int) ($headerWidget?->header_height ?? 0), 0);
    $headerWidth = max((int) ($headerWidget?->header_width ?? 0), 0);

    $headerStyle = collect([
        filled($headerBackgroundColor) ? "background-color: {$headerBackgroundColor};" : null,
        $headerHeight > 0 ? "min-height: {$headerHeight}px;" : null,
        $headerWidth > 0 ? "max-width: {$headerWidth}px; margin-left: auto; margin-right: auto;" : null,
    ])->filter()->implode(' ');

    $brandPrimary = $headerWidget?->brand_primary ?: 'HOT';
    $brandAccent = $headerWidget?->brand_accent ?: 'ESCORTS';

    $topLeftItems = collect($headerWidget?->top_left_items ?? [
        ['label' => 'Verified advertisers', 'icon' => 'fa-solid fa-shield-heart'],
        ['label' => 'Australia-wide directory', 'icon' => 'fa-solid fa-location-dot'],
    ])->filter(fn ($item) => filled($item['label'] ?? null))->values();

    $topRightLinks = collect($headerWidget?->top_right_links ?? [
        ['label' => 'Help', 'url' => route('help')],
        ['label' => 'Complaints/Contact', 'url' => route('complaints-contact')],
    ])
        ->map(function ($item) {
            $label = strtolower(trim((string) ($item['label'] ?? '')));

            if ($label === 'help') {
                $item['url'] = route('help');
            }

            return $item;
        })
        ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))
        ->values();

    $actionLinks = collect($headerWidget?->action_links ?? [
        ['label' => 'Pricing', 'url' => url('/pricing')],
        ['label' => 'Credit Packages', 'url' => url('/membership')],
        ['label' => 'Advertiser Login', 'url' => url('/signin')],
        ['label' => 'Advertiser Register', 'url' => url('/signup')],
    ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null));

    $currentUser = auth()->user();
    $isAuthenticated = filled($currentUser);
    $isAdminAuthenticated = $isAuthenticated && in_array($currentUser->role ?? null, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_REVIEWER], true);
    $primaryAuthUrl = $isAdminAuthenticated ? filament()->getUrl() : url('/my-profile');
    $primaryAuthLabel = $isAdminAuthenticated ? 'Dashboard' : 'My Profile';
    $isAddAdvertisementLink = function ($item): bool {
        return strtolower(trim((string) ($item['label'] ?? ''))) === 'add advertisement';
    };

    if ($isAuthenticated) {
        $actionLinks = $actionLinks->map(function ($item) use ($isAddAdvertisementLink, $primaryAuthUrl) {
            if ($isAddAdvertisementLink($item)) {
                $item['url'] = $primaryAuthUrl;
            }

            return $item;
        });

        $actionLinks = $actionLinks->reject(function ($item): bool {
            $label = strtolower(trim((string) ($item['label'] ?? '')));
            $rawUrl = strtolower(trim((string) ($item['url'] ?? '')));
            $path = strtolower((string) (parse_url($rawUrl, PHP_URL_PATH) ?: $rawUrl));

            return str_contains($label, 'login')
                || str_contains($label, 'sign in')
                || str_contains($label, 'sign up')
                || str_contains($label, 'join')
                || in_array($path, ['/login', '/signin', '/signup', '/register'], true);
        });
    } else {
        $actionLinks = $actionLinks->reject($isAddAdvertisementLink);
    }

    $actionLinks = $actionLinks->values();

    $mainNavLinks = collect($headerWidget?->main_nav_links ?? [
        ['label' => 'Home', 'url' => url('/')],
        ['label' => 'About us', 'url' => route('about-us')],
        ['label' => 'Escorts', 'url' => route('escorts.search')],
        ['label' => 'Pricing', 'url' => url('/pricing')],
        ['label' => 'How credits work', 'url' => route('how-credits-work')],
        ['label' => 'Sign Up', 'url' => url('/signup')],
        ['label' => 'Sign In', 'url' => url('/signin')],
    ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

    $hasPricingInMainNav = $mainNavLinks->contains(function ($item) {
        $label = strtolower(trim((string) ($item['label'] ?? '')));
        $url = trim((string) ($item['url'] ?? ''));

        return $label === 'pricing' || $url === url('/pricing');
    });

    if (! $hasPricingInMainNav) {
        $mainNavLinks->push([
            'label' => 'Pricing',
            'url' => url('/pricing'),
        ]);
    }

    if ($isAuthenticated) {
        $mainNavLinks = $mainNavLinks->reject(function ($item): bool {
            $label = strtolower(trim((string) ($item['label'] ?? '')));
            $rawUrl = strtolower(trim((string) ($item['url'] ?? '')));
            $path = strtolower((string) (parse_url($rawUrl, PHP_URL_PATH) ?: $rawUrl));

            return in_array($label, ['my profile', 'dashboard', 'login', 'sign in', 'sign up', 'join now'], true)
                || in_array($path, ['/my-profile', '/edit-profile', '/admin', '/login', '/signin', '/signup', '/register'], true);
        })->values();

        $authNavItem = [
            'label' => $primaryAuthLabel,
            'url' => $primaryAuthUrl,
        ];

        $logoutNavItem = [
            'label' => 'Logout',
            'url' => '#',
            'is_logout' => true,
        ];

        $mainNavLinks = $mainNavLinks->concat([$authNavItem, $logoutNavItem])->values();
    }

    $mobileExtraLinks = collect($headerWidget?->mobile_extra_links ?? [
        ['label' => 'Report a Listing', 'url' => route('report-a-listing')],
    ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

    $showTopBar = $headerWidget?->enable_top_bar ?? false;
    $showSearch = $headerWidget?->enable_search ?? true;
    $isGirlProfilePage = request()->routeIs('profile.show');
    $showFreeTrialCta = (bool) ($headerWidget?->show_free_trial_cta ?? true);
    $freeTrialCtaText = trim((string) ($headerWidget?->free_trial_cta_text ?? 'Get 21 days for free'));
    $freeTrialCtaUrl = trim((string) ($headerWidget?->free_trial_cta_url ?? url('/signup')));

    $escortMenuLinks = $escortCities
        ->map(function ($city) {
            $suburb = trim((string) ($city->suburb ?? $city['suburb'] ?? ''));
            $state = trim((string) ($city->state ?? $city['state'] ?? ''));
            $locationSlug = \Illuminate\Support\Str::slug($suburb).($state !== '' ? '-'.strtolower($state) : '');

            return [
                'type' => 'city',
                'label' => "{$suburb} Escorts",
                'suburb' => $suburb,
                'state' => $state,
                'url' => route('escorts.search.slug', ['location_slug' => $locationSlug]),
                'search' => \Illuminate\Support\Str::lower(trim("{$suburb} {$state} escorts")),
            ];
        })
        ->concat(collect(\App\Support\EscortLocationData::extraMenuLinks())->map(fn (array $item) => [
            ...$item,
            'type' => 'link',
            'search' => \Illuminate\Support\Str::lower($item['label']),
        ]))
        ->values();

    $normalizePath = function (string $url): string {
        $path = '/'.trim((string) (parse_url($url, PHP_URL_PATH) ?? ''), '/');

        return $path === '//' ? '/' : $path;
    };

    $currentPath = $normalizePath(url()->current());
    $hasQueryString = request()->getQueryString() !== null;

    $isNavItemActive = function (string $url) use ($currentPath, $hasQueryString, $normalizePath): bool {
        $itemPath = $normalizePath($url);

        if ($itemPath === '/') {
            return $currentPath === '/' && ! $hasQueryString;
        }

        return $itemPath === $currentPath;
    };

    $desktopNavLinks = $mainNavLinks->reject(function ($item): bool {
        $label = strtolower(trim((string) ($item['label'] ?? '')));

        return in_array($label, ['sign up', 'sign in', 'my profile', 'dashboard', 'logout'], true);
    })->values();

    $primaryActionLink = $actionLinks->first(function ($item): bool {
        return strtolower(trim((string) ($item['label'] ?? ''))) === 'add advertisement';
    });

    $authDisplayName = $isAuthenticated ? trim((string) ($currentUser->name ?? '')) : null;

    if ($isAuthenticated && $authDisplayName === '') {
        $authDisplayName = trim((string) ($currentUser->email ?? ''));
    }

    $authDisplayName = filled($authDisplayName) ? $authDisplayName : 'Account';

    $authMenuGroups = $isAuthenticated ? collect([
        [
            'title' => 'My Account',
            'items' => [
                ['label' => 'Account settings', 'url' => route('my-account')],
                ['label' => 'Registered email', 'url' => route('change-email')],
                ['label' => 'Change password', 'url' => route('change-password')],
                ['label' => 'Delete account', 'url' => route('account.delete-page')],
            ],
        ],
        [
            'title' => 'Profile',
            'items' => [
                ['label' => 'Profile verification', 'url' => route('verify.photos')],
                ['label' => 'Edit profile', 'url' => route('edit-profile')],
                ['label' => 'Photos & gallery', 'url' => route('photos')],
                ['label' => 'Videos', 'url' => route('my-videos')],
                ['label' => 'Availability settings', 'url' => route('availability.edit')],
            ],
        ],
        [
            'title' => 'My Listings',
            'items' => [
                ['label' => 'All listings', 'url' => route('my-listings')],
                ['label' => 'Promotions', 'url' => route('featured')],
                ['label' => 'Payment history', 'url' => route('payment-subscription')],
            ],
        ],
        [
            'title' => null,
            'items' => [
                ['label' => 'Privacy settings', 'url' => route('privacy-policy')],
                ['label' => 'Help & support', 'url' => route('help')],
            ],
        ],
    ]) : collect();

    $authDropdownItems = $isAuthenticated ? collect([
        ['label' => 'My Account', 'url' => route('my-account')],
        ['label' => 'My Profiles', 'url' => route('profiles.index')],
        ['label' => 'My Listings', 'url' => route('my-listings'), 'is_active' => request()->routeIs('my-listings')],
        ['label' => 'Billing & Payments', 'url' => route('payment-subscription')],
        ['label' => 'Privacy Settings', 'url' => route('privacy-policy')],
        ['label' => 'Help & Support', 'url' => route('help'), 'divider_before' => true],
    ]) : collect();
@endphp

<header id="main-header" class="sticky top-0 z-50 relative border-b border-gray-800 bg-gray-950" style="{{ $headerStyle }}">
@if($showTopBar)
    <div class="hidden border-b border-gray-800 bg-gray-950 lg:block">
        <div class="mx-auto flex w-full max-w-12xl items-center justify-between bg-slate-900 px-4 py-2 text-sm text-white sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                @foreach($topLeftItems as $item)
                    <span class="inline-flex items-center gap-2">
                        @if(filled($item['icon'] ?? null))
                            <i class="{{ $item['icon'] }} text-pink-500"></i>
                        @endif
                        {{ $item['label'] }}
                    </span>
                @endforeach
            </div>

            <div class="flex items-center gap-6">
                @foreach($topRightLinks as $item)
                    <a href="{{ $item['url'] }}" class="transition hover:text-pink-400">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="mx-auto w-full max-w-12xl px-4 sm:px-6 lg:px-8">
    <div class="flex min-h-[70px] items-center justify-between gap-4 py-3 {{ $isGirlProfilePage ? 'md:hidden' : 'lg:hidden' }}">
        <a href="{{ url('/') }}" class="shrink-0">
            @if($logoType === 'image' && filled($logoUrl))
                <img src="{{ $logoUrl }}" alt="Site Logo" class="h-auto w-auto" style="{{ $logoStyle }}" loading="lazy" decoding="async">
            @else
                <span class="text-2xl font-bold">{{ $brandPrimary }}<span class="text-pink-500">{{ $brandAccent }}</span></span>
            @endif
        </a>

        <form action="{{ route('escorts.search') }}" method="GET" class="{{ $showSearch ? 'hidden flex-1 xl:block' : 'hidden' }}">
            <div class="flex max-w-2xl items-center rounded-xl border border-gray-700 bg-gray-800/80 p-1.5">
                <div class="flex min-w-0 flex-1 items-center gap-2 px-2">
                    <i class="fa-solid fa-magnifying-glass text-gray-500"></i>
                    <input type="text" name="escort_name" placeholder="Search by name or keyword" class="w-full border-0 bg-transparent text-sm text-white placeholder:text-gray-500 focus:outline-none focus:ring-0">
                </div>

                <div class="hidden items-center gap-2 border-l border-gray-700 px-3 lg:flex">
                    <i class="fa-solid fa-location-dot text-gray-500"></i>
                    <input type="text" name="location" placeholder="City" class="w-28 border-0 bg-transparent text-sm text-white placeholder:text-gray-500 focus:outline-none focus:ring-0">
                </div>

                <button type="submit" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">
                    Search
                </button>
            </div>
        </form>

        <div class="hidden items-center space-x-4 md:flex {{ $isGirlProfilePage ? '' : 'lg:hidden' }}">
            @foreach($actionLinks as $item)
                <a href="{{ $item['url'] }}" class="text-sm font-medium text-gray-300 transition hover:text-pink-400">
                    {{ $item['label'] }}
                </a>
            @endforeach

            @guest
                <a href="{{ url('/signup') }}" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">
                    Join Now
                </a>
            @endguest
        </div>

        <button @click="mobileMenu = !mobileMenu" class="{{ $isGirlProfilePage ? 'md:hidden' : 'lg:hidden' }} text-gray-300 hover:text-white" aria-label="Toggle menu">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <div class="hidden items-center gap-4 border-t border-gray-800 py-3 {{ $isGirlProfilePage ? 'md:flex' : 'lg:flex' }}">
        <a href="{{ url('/') }}" class="shrink-0">
            @if($logoType === 'image' && filled($logoUrl))
                <img src="{{ $logoUrl }}" alt="Site Logo" class="h-auto w-auto" style="{{ $logoStyle }}" loading="lazy" decoding="async">
            @else
                <span class="text-xl font-bold text-white">{{ $brandPrimary }}<span class="text-pink-500">{{ $brandAccent }}</span></span>
            @endif
        </a>

        <div class="flex shrink-0 items-center gap-2 whitespace-nowrap">
        </div>

        <nav class="flex min-w-0 flex-1 flex-nowrap items-center gap-1">
            @foreach($desktopNavLinks as $item)
                @if(strtolower($item['label']) === 'escorts')
                    <div
                        class="relative"
                        x-data="{ open: false, search: '', links: {{ \Illuminate\Support\Js::from($escortMenuLinks->all(), JSON_UNESCAPED_SLASHES) }}, get filteredLinks() { const term = this.search.toLowerCase().trim(); return term ? this.links.filter((link) => link.search.includes(term)) : this.links; }, get filteredCityLinks() { return this.filteredLinks.filter(l => l.type === 'city'); }, get filteredExtraLinks() { return this.filteredLinks.filter(l => l.type === 'link'); } }"
                        @click.outside="open = false; search = ''"
                    >
                        <button @click="open = !open; if (! open) { search = ''; }" type="button" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium hover:text-white">
                            {{ $item['label'] }}
                            <i class="fa-solid fa-chevron-down ml-1 text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute left-0 z-50 mt-2 w-[560px] max-h-[520px] overflow-y-auto rounded-xl bg-white shadow-[0_12px_30px_rgba(15,23,42,0.18)] ring-1 ring-black/5"
                            style="display:none;"
                        >
                            {{-- Search Input --}}
                            <div class="sticky top-0 bg-white px-4 pt-4 pb-3 z-10">
                                <label for="escort-menu-search" class="sr-only">Search escorts menu</label>
                                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                    <i class="fa-solid fa-magnifying-glass text-xs text-gray-400"></i>
                                    <input id="escort-menu-search" x-model.live.debounce.150ms="search" type="text" placeholder="Search by city…" class="w-full border-0 bg-transparent text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-0">
                                    <button x-show="search !== ''" @click="search = ''" type="button" class="text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Location Cards --}}
                            <div class="border-t border-gray-100 px-4 pt-3 pb-4">
                                <p x-show="filteredCityLinks.length === 0 && filteredExtraLinks.length === 0" class="py-4 text-center text-sm text-gray-500">
                                    No matching locations found.
                                </p>

                                <div x-show="filteredCityLinks.length > 0">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Browse by City</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="link in filteredCityLinks" :key="link.url">
                                            <a
                                                @click="open = false; search = ''"
                                                :href="link.url"
                                                class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 transition hover:border-pink-300 hover:bg-pink-50"
                                            >
                                                <i class="fa-solid fa-location-dot mt-0.5 shrink-0 text-pink-500 text-xs"></i>
                                                <span>
                                                    <span x-text="link.suburb" class="block text-sm font-medium text-gray-800 leading-tight"></span>
                                                    <span x-text="link.state" class="text-[11px] text-gray-500"></span>
                                                </span>
                                            </a>
                                        </template>
                                    </div>
                                </div>

                                {{-- Extra / Quick Links --}}
                                <div x-show="filteredExtraLinks.length > 0" :class="filteredCityLinks.length > 0 ? 'mt-4 border-t border-gray-100 pt-4' : ''">
                                    <p x-show="filteredCityLinks.length > 0" class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Quick Links</p>
                                    <div class="grid grid-cols-2 gap-1">
                                        <template x-for="link in filteredExtraLinks" :key="link.url">
                                            <a @click="open = false; search = ''" :href="link.url" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 hover:text-pink-700">
                                                <i class="fa-solid fa-arrow-right text-xs text-gray-400"></i>
                                                <span x-text="link.label"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @php $isActive = $isNavItemActive((string) $item['url']); @endphp

                    <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium transition {{ $isActive ? 'text-white' : ' hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            @if($primaryActionLink)
                <a href="{{ $primaryActionLink['url'] }}" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-1 rounded mx-4">
                    {{ $primaryActionLink['label'] }}
                </a>
            @endif

            @auth
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button
        @click="open = !open"
        type="button"
        class="bg-yellow-400 text-slate-900 px-3 py-1 rounded hover:bg-yellow-500 flex items-center gap-1"
    >
        {{ $authDisplayName }}
        <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
    </button>

    <div
        x-cloak
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute top-full z-50 w-[340px] overflow-hidden rounded-xl bg-white py-3 shadow-[0_12px_30px_rgba(15,23,42,0.18)] ring-1 ring-black/5"
        style="display:none;"
    >
        @foreach($authDropdownItems as $item)
            @if($item['divider_before'] ?? false)
                <div class="my-3 border-t border-gray-200"></div>
            @endif

            <a
                href="{{ $item['url'] }}"
                @click="open = false"
                class="block whitespace-nowrap px-5 py-3 text-[18px] leading-tight text-black transition hover:bg-gray-50 {{ ($item['is_active'] ?? false) ? 'font-bold' : 'font-normal' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach

        <div class="my-3 border-t border-gray-200"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="button"
                        class="block w-full px-5 py-3 text-left text-[18px] font-normal leading-tight text-red-600 transition hover:bg-gray-50"
                        @click="open = false; confirmSignOut($el.closest('form'))"
                    >
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
                    @else
                <a href="{{ route('signin') }}" class="mx-4 text-sm font-medium text-white transition hover:text-white">
                    Sign In
                </a>

                <a href="{{ url('/signup') }}" class="inline-flex items-center rounded-full bg-pink-600 mx-4 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">
                    Sign Up
                </a>
            @endauth
        </nav>

        <div class="flex items-center gap-6">
            <a href="{{ route('favourites') }}" class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-3 py-2 text-sm font-medium  transition hover:bg-gray-800 hover:text-pink-400" title="My Favourites">
                <i class="fa-solid fa-heart text-pink-500"></i>
                <span class="hidden sm:inline">Favourites</span>
            </a>
            @if($showFreeTrialCta && filled($freeTrialCtaText) && filled($freeTrialCtaUrl))
                <a href="{{ $freeTrialCtaUrl }}" class="hover:text-pink-500">
                    {{ $freeTrialCtaText }}
                </a>
            @endif
        </div>
    </div>

    <div x-cloak x-show="mobileMenu" x-transition class="space-y-3 border-t border-gray-800 py-4 {{ $isGirlProfilePage ? 'md:hidden' : 'lg:hidden' }}">
        @if($showSearch && ! $isGirlProfilePage)
            <form action="{{ url('/') }}" method="GET" class="px-3 pb-1" @submit="mobileMenu = false">
                <div class="flex items-center gap-2 rounded-lg border border-gray-700 bg-gray-800 px-3 py-2">
                    <i class="fa-solid fa-magnifying-glass shrink-0 text-xs text-gray-500"></i>
                    <input type="text" name="location" placeholder="Search escorts by location…" class="min-w-0 flex-1 border-0 bg-transparent text-sm text-white placeholder:text-gray-500 focus:outline-none focus:ring-0">
                    <button type="submit" class="shrink-0 text-xs font-semibold text-pink-400 hover:text-pink-300">
                        Go
                    </button>
                </div>
            </form>
        @endif

        <div class="space-y-1 text-sm">
            @foreach($actionLinks as $item)
                <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">
                    {{ $item['label'] }}
                </a>
            @endforeach

            @if($showFreeTrialCta && filled($freeTrialCtaText) && filled($freeTrialCtaUrl))
                <a @click="mobileMenu = false" href="{{ $freeTrialCtaUrl }}" class="block rounded-lg px-3 py-2 text-pink-200 hover:bg-pink-500/10 hover:text-white">
                    {{ $freeTrialCtaText }}
                </a>
            @endif

            <a @click="mobileMenu = false" href="{{ route('favourites') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('favourites') ? 'bg-gray-800 font-medium text-pink-400' : 'text-gray-200 hover:bg-gray-800' }}">
                <i class="fa-solid fa-heart mr-1.5 text-xs text-pink-500"></i>
                Favourites
            </a>

            <div class="border-t border-gray-800"></div>

            @foreach($mainNavLinks as $item)
                @if(!empty($item['is_logout']))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="button" class="block w-full rounded-lg px-3 py-2 text-left text-gray-200 hover:bg-gray-800" @click="confirmSignOut($el.closest('form'))">
                            Sign Out
                        </button>
                    </form>
                @elseif(strtolower($item['label']) === 'escorts')
                    <div x-data="{ open: false, search: '', links: {{ \Illuminate\Support\Js::from($escortMenuLinks->all(), JSON_UNESCAPED_SLASHES) }}, get filteredLinks() { const term = this.search.toLowerCase().trim(); return term ? this.links.filter((link) => link.search.includes(term)) : this.links; }, get filteredCityLinks() { return this.filteredLinks.filter(l => l.type === 'city'); }, get filteredExtraLinks() { return this.filteredLinks.filter(l => l.type === 'link'); } }">
                        <button @click="open = !open" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">
                            <span>{{ $item['label'] }}</span>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-cloak x-show="open" x-transition class="ml-3 mt-1 space-y-0.5 border-l border-gray-700 pl-3">
                            <div class="pr-3 pt-2">
                                <label for="mobile-escort-menu-search" class="sr-only">Search escorts menu</label>
                                <div class="flex items-center gap-2 rounded-lg border border-gray-700 bg-gray-900 px-3 py-2">
                                    <i class="fa-solid fa-magnifying-glass text-xs text-gray-500"></i>
                                    <input id="mobile-escort-menu-search" x-model.live.debounce.150ms="search" type="text" placeholder="Search by city…" class="w-full border-0 bg-transparent text-sm text-white placeholder:text-gray-500 focus:outline-none focus:ring-0">
                                </div>
                            </div>

                            <template x-for="link in filteredCityLinks" :key="link.url">
                                <a @click="mobileMenu = false; open = false; search = ''" :href="link.url" class="flex items-center gap-2 rounded-lg px-3 py-2 text-gray-300 hover:bg-gray-800">
                                    <i class="fa-solid fa-location-dot shrink-0 text-pink-500 text-xs"></i>
                                    <span>
                                        <span x-text="link.suburb" class="font-medium"></span>
                                        <span x-text="link.state ? ' · ' + link.state : ''" class="text-xs text-gray-500"></span>
                                    </span>
                                </a>
                            </template>

                            <template x-if="filteredExtraLinks.length > 0">
                                <div :class="filteredCityLinks.length > 0 ? 'mt-1 border-t border-gray-700 pt-1' : ''">
                                    <template x-for="link in filteredExtraLinks" :key="link.url">
                                        <a @click="mobileMenu = false; open = false; search = ''" :href="link.url" class="flex items-center gap-2 rounded-lg px-3 py-2 text-gray-400 hover:bg-gray-800 hover:text-gray-200">
                                            <i class="fa-solid fa-arrow-right text-xs"></i>
                                            <span x-text="link.label"></span>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <p x-show="filteredCityLinks.length === 0 && filteredExtraLinks.length === 0" class="rounded-lg px-3 py-2 text-sm text-gray-500">
                                No matching locations found.
                            </p>
                        </div>
                    </div>
                @else
                    @php $isActive = $isNavItemActive((string) $item['url']); @endphp

                    <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 {{ $isActive ? 'bg-gray-800 font-medium text-white' : 'text-gray-200 hover:bg-gray-800' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            @auth
                <div class="border-t border-gray-800 px-3 pt-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-gray-500">
                        Account
                    </p>

                    @foreach($authMenuGroups as $group)
                        @if(filled($group['title']))
                            <p class="mt-3 px-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">
                                {{ $group['title'] }}
                            </p>
                        @endif

                        @foreach($group['items'] as $item)
                            <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="button" @click="mobileMenu = false; confirmSignOut($el.closest('form'))" class="mt-3 block w-full rounded-lg px-3 py-2 text-left text-gray-200 hover:bg-gray-800">
                            Sign Out
                        </button>
                    </form>
                </div>
            @endauth

            @foreach($mobileExtraLinks as $item)
                <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($reviewerMode ?? false)
    <div class="flex items-center justify-center gap-2 bg-amber-500 px-4 py-2 text-center text-sm font-semibold text-white shadow-md">
        <i class="fa-solid fa-eye"></i>
        <span>Read-Only Reviewer Mode — You are viewing a demo account. No changes can be made.</span>
    </div>
@endif
</header>
