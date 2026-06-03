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
        ['label' => 'Follow Alice', 'url' => route('blog')],
        ['label' => 'Help', 'url' => route('help')],
        ['label' => 'Contact', 'url' => route('contact-us')],
    ])
        ->map(function ($item) {
            if (strtolower(trim((string) ($item['label'] ?? ''))) === 'help') {
                $item['url'] = route('help');
            }

            return $item;
        })
        ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))
        ->values();

    $actionLinks = collect($headerWidget?->action_links ?? [
        ['label' => 'Add Advertisement', 'url' => url('/signup')],
    ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null));

    $currentUser = auth()->user();
    $isAuthenticated = filled($currentUser);
    $isAdminAuthenticated = $isAuthenticated && (($currentUser->role ?? null) === \App\Models\User::ROLE_ADMIN);

    $primaryAuthUrl = $isAdminAuthenticated ? filament()->getUrl() : url('/my-profile');
    $primaryAuthLabel = $isAdminAuthenticated ? 'Dashboard' : 'My Profile';

    $isAddAdvertisementLink = function ($item): bool {
        $label = strtolower(trim((string) ($item['label'] ?? '')));

        return in_array($label, ['add advertisement', 'add advert', 'add ad'], true);
    };

    if ($isAuthenticated) {
        $actionLinks = $actionLinks->map(function ($item) use ($isAddAdvertisementLink, $primaryAuthUrl) {
            if ($isAddAdvertisementLink($item)) {
                $item['url'] = $primaryAuthUrl;
                $item['label'] = 'Add Advertisement';
            }

            return $item;
        });
    } else {
        $actionLinks = $actionLinks->reject($isAddAdvertisementLink);
    }

    $actionLinks = $actionLinks->values();

    $mainNavLinks = collect($headerWidget?->main_nav_links ?? [
        ['label' => 'Home', 'url' => url('/')],
        ['label' => 'About us', 'url' => route('about-us')],
        ['label' => 'Pricing', 'url' => url('/pricing')],
        ['label' => 'Escorts', 'url' => url('/')],
        ['label' => 'Naughty corner', 'url' => route('naughty-corner')],
        ['label' => 'Blog', 'url' => route('blog')],
    ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

    $mobileExtraLinks = collect($headerWidget?->mobile_extra_links ?? [
        ['label' => 'Contact', 'url' => route('contact-us')],
    ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

    $showTopBar = (bool) ($headerWidget?->enable_top_bar ?? true);
    $showSearch = (bool) ($headerWidget?->enable_search ?? true);
    $isGirlProfilePage = request()->routeIs('profile.show');

    $showFreeTrialCta = (bool) ($headerWidget?->show_free_trial_cta ?? true);
    $freeTrialCtaText = trim((string) ($headerWidget?->free_trial_cta_text ?? 'Get 21 days for free'));
    $freeTrialCtaUrl = trim((string) ($headerWidget?->free_trial_cta_url ?? url('/signup')));

    $defaultEscortCities = collect([
        ['suburb' => 'Brisbane', 'state' => 'QLD'],
        ['suburb' => 'Sydney', 'state' => 'NSW'],
        ['suburb' => 'Melbourne', 'state' => 'VIC'],
        ['suburb' => 'Adelaide', 'state' => 'SA'],
        ['suburb' => 'Canberra', 'state' => 'ACT'],
        ['suburb' => 'Perth', 'state' => 'WA'],
        ['suburb' => 'Darwin', 'state' => 'NT'],
        ['suburb' => 'Gold Coast', 'state' => 'QLD'],
        ['suburb' => 'Sunshine Coast', 'state' => 'QLD'],
        ['suburb' => 'Newcastle', 'state' => 'NSW'],
        ['suburb' => 'Cairns', 'state' => 'QLD'],
        ['suburb' => 'Hobart', 'state' => 'TAS'],
    ]);

    $escortMenuLinks = ($escortCities->isNotEmpty() ? $escortCities : $defaultEscortCities)
        ->map(function ($city) {
            $suburb = trim((string) ($city->suburb ?? $city['suburb'] ?? ''));
            $state = trim((string) ($city->state ?? $city['state'] ?? ''));
            $location = trim(collect([$suburb, $state])->filter()->implode(', '));

            return [
                'label' => "{$suburb} escorts",
                'url' => url('/?location='.urlencode($location)),
                'search' => \Illuminate\Support\Str::lower(trim("{$suburb} {$state} escorts")),
            ];
        })
        ->concat(collect([
            ['label' => 'Touring escorts', 'url' => url('/advanced-search')],
            ['label' => 'Escorts directory', 'url' => url('/')],
            ['label' => 'Search for escorts', 'url' => route('advanced-search')],
            ['label' => 'Escorts near me', 'url' => url('/advanced-search')],
            ['label' => 'View all our escorts', 'url' => url('/')],
        ])->map(fn (array $item) => [
            ...$item,
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

    $primaryActionLink = $actionLinks->first($isAddAdvertisementLink);

    $authDisplayName = $isAuthenticated ? trim((string) ($currentUser->name ?? '')) : null;

    if ($isAuthenticated && $authDisplayName === '') {
        $authDisplayName = trim((string) ($currentUser->email ?? ''));
    }

    $authDisplayName = filled($authDisplayName) ? $authDisplayName : 'Account';

    $authDropdownItems = $isAuthenticated ? collect([
        ['label' => 'My Account', 'url' => route('my-profile')],
        ['label' => 'My Profiles', 'url' => route('profiles.index'), 'is_active' => request()->routeIs('profiles.index')],
        ['label' => 'My Listings', 'url' => route('my-listings'), 'is_active' => request()->routeIs('my-listings')],
        ['label' => 'Billing & Payments', 'url' => route('payment-subscription')],
        ['label' => 'Privacy Settings', 'url' => route('privacy-policy')],
        ['label' => 'Help & Support', 'url' => route('help'), 'divider_before' => true],
    ]) : collect();

    $authMenuGroups = $isAuthenticated ? collect([
        [
            'title' => 'My Account',
            'items' => [
                ['label' => 'Account settings', 'url' => route('my-profile')],
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
@endphp

<header id="main-header" class="w-full border-b border-slate-800 bg-slate-900" style="{{ $headerStyle }}">
    @if($showTopBar)
        <div class="hidden h-[30px] bg-[#070e22] lg:block">
            <div class="flex h-full max-w-[1600px] items-center justify-between px-6 text-[12px] font-medium text-white">
                <div class="flex items-center gap-5">
                    @foreach($topLeftItems as $item)
                        <span class="inline-flex items-center gap-2">
                            @if(filled($item['icon'] ?? null))
                                <i class="{{ $item['icon'] }} text-pink-500"></i>
                            @endif
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </div>

                <div class="flex gap-5">
                    @foreach($topRightLinks as $item)
                        <a href="{{ $item['url'] }}" class="text-white transition hover:text-pink-400">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="bg-[#172236]">
        <div class="max-w-[1600px] px-6">
            <div class="flex min-h-[54px] items-center justify-between gap-4 {{ $isGirlProfilePage ? 'md:hidden' : 'lg:hidden' }}">
                <a href="{{ url('/') }}" class="shrink-0">
                    @if($logoType === 'image' && filled($logoUrl))
                        <img src="{{ $logoUrl }}" alt="Site Logo" class="h-auto w-auto" style="{{ $logoStyle }}" loading="lazy" decoding="async">
                    @else
                        <span class="text-[22px] font-extrabold leading-none tracking-tight text-white">
                            {{ $brandPrimary }}<span class="text-pink-500">{{ $brandAccent }}</span>
                        </span>
                    @endif
                </a>

                <button @click="mobileMenu = !mobileMenu" class="text-white hover:text-pink-400" aria-label="Toggle menu">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>

            <div class="hidden min-h-[54px] items-center justify-between gap-5 {{ $isGirlProfilePage ? 'md:flex' : 'lg:flex' }}">
                <div class="flex min-w-0 items-center gap-8">
                    <a href="{{ url('/') }}" class="shrink-0">
                        @if($logoType === 'image' && filled($logoUrl))
                            <img src="{{ $logoUrl }}" alt="Site Logo" class="h-auto w-auto" style="{{ $logoStyle }}" loading="lazy" decoding="async">
                        @else
                            <span class="text-[22px] font-extrabold leading-none tracking-tight text-white">
                                {{ $brandPrimary }}<span class="text-pink-500">{{ $brandAccent }}</span>
                            </span>
                        @endif
                    </a>

                    <nav class="flex min-w-0 flex-nowrap items-center gap-1">
                        @foreach($desktopNavLinks as $item)
                            @if(strtolower($item['label']) === 'escorts')
                                <div
                                    class="relative"
                                    x-data="{ open: false, search: '', links: {{ \Illuminate\Support\Js::from($escortMenuLinks->all()) }}, get filteredLinks() { const term = this.search.toLowerCase().trim(); return term ? this.links.filter((link) => link.search.includes(term)) : this.links; } }"
                                    @click.outside="open = false; search = ''"
                                >
                                    <button
                                        @click="open = !open; if (! open) { search = ''; }"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-2 text-[14px] font-semibold text-white transition hover:bg-white/10"
                                    >
                                        {{ $item['label'] }}
                                        <i class="fa-solid fa-chevron-down ml-1 text-[10px] transition-transform" :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="open"
                                        x-transition
                                        class="absolute left-0 z-50 mt-2 max-h-80 w-72 overflow-y-auto rounded-lg bg-[#172236] py-2 shadow-xl ring-1 ring-white/10"
                                        style="display:none;"
                                    >
                                        <div class="px-3 pb-2">
                                            <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-[#070e22] px-3 py-2">
                                                <i class="fa-solid fa-magnifying-glass text-xs text-gray-400"></i>
                                                <input x-model.live.debounce.150ms="search" type="text" placeholder="Search escorts menu" class="w-full border-0 bg-transparent text-sm text-white placeholder:text-gray-400 focus:outline-none focus:ring-0">
                                            </div>
                                        </div>

                                        <div class="border-t border-white/10 pt-2">
                                            <template x-for="link in filteredLinks" :key="`${link.label}-${link.url}`">
                                                <a @click="open = false; search = ''" :href="link.url" class="block px-5 py-2 text-sm text-white hover:bg-white/10">
                                                    <span x-text="link.label"></span>
                                                </a>
                                            </template>

                                            <p x-show="filteredLinks.length === 0" class="px-5 py-2 text-sm text-gray-400">
                                                No matching escorts found.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @php $isActive = $isNavItemActive((string) $item['url']); @endphp

                                <a
                                    href="{{ $item['url'] }}"
                                    class="inline-flex items-center rounded-md px-2.5 py-2 text-[14px] font-semibold transition {{ $isActive ? 'bg-white/10 text-white' : 'text-white hover:bg-white/10' }}"
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </nav>
                </div>

                <div class="flex shrink-0 items-center gap-5 whitespace-nowrap">
                    @if($primaryActionLink)
                        <a href="{{ $primaryActionLink['url'] }}" class="inline-flex h-10 items-center rounded-md bg-pink-500 px-6 text-[14px] font-bold text-white transition hover:bg-pink-600">
                            {{ $primaryActionLink['label'] }}
                        </a>
                    @endif

                    @auth
                        <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                            <button
                                @click="open = !open"
                                type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-md bg-yellow-400 px-4 text-[14px] font-bold text-black transition hover:bg-yellow-300"
                            >
                                {{ $authDisplayName }}
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-0 top-full z-50 mt-2 w-[250px] overflow-hidden rounded-xl bg-white py-3 shadow-[0_12px_30px_rgba(15,23,42,0.18)] ring-1 ring-black/5"
                                style="display:none;"
                            >
                                @foreach($authDropdownItems as $item)
                                    @if($item['divider_before'] ?? false)
                                        <div class="my-3 border-t border-gray-200"></div>
                                    @endif

                                    <a
                                        href="{{ $item['url'] }}"
                                        @click="open = false"
                                        class="block px-5 py-3 text-[18px] leading-tight text-black transition hover:bg-gray-50 {{ ($item['is_active'] ?? false) ? 'font-bold' : 'font-normal' }}"
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
                        <a href="{{ route('signin') }}" class="text-[14px] font-semibold text-white transition hover:text-pink-400">
                            Sign In
                        </a>

                        <a href="{{ url('/signup') }}" class="inline-flex h-10 items-center rounded-md bg-pink-500 px-5 text-[14px] font-bold text-white transition hover:bg-pink-600">
                            Sign Up
                        </a>
                    @endauth

                    <a href="{{ route('favourites') }}" class="inline-flex items-center gap-2 text-[14px] font-bold text-white transition hover:text-pink-400" title="My Favourites">
                        <i class="fa-solid fa-heart text-pink-500"></i>
                        <span>Favourites</span>
                    </a>

                    @if($showFreeTrialCta && filled($freeTrialCtaText) && filled($freeTrialCtaUrl))
                        <a href="{{ $freeTrialCtaUrl }}" class="inline-flex items-center text-[14px] font-bold text-white transition hover:text-pink-400">
                            {{ $freeTrialCtaText }}
                        </a>
                    @endif
                </div>
            </div>

            <div x-cloak x-show="mobileMenu" x-transition class="space-y-3 border-t border-white/10 py-4 {{ $isGirlProfilePage ? 'md:hidden' : 'lg:hidden' }}">
                @if($showSearch && ! $isGirlProfilePage)
                    <form action="{{ url('/') }}" method="GET" class="px-3 pb-1" @submit="mobileMenu = false">
                        <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-[#070e22] px-3 py-2">
                            <i class="fa-solid fa-magnifying-glass shrink-0 text-xs text-gray-400"></i>
                            <input type="text" name="location" placeholder="Search escorts by location…" class="min-w-0 flex-1 border-0 bg-transparent text-sm text-white placeholder:text-gray-400 focus:outline-none focus:ring-0">
                            <button type="submit" class="shrink-0 text-xs font-semibold text-pink-400 hover:text-pink-300">
                                Go
                            </button>
                        </div>
                    </form>
                @endif

                <div class="space-y-1 text-sm">
                    @foreach($topRightLinks as $item)
                        <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <div class="border-t border-white/10"></div>

                    @foreach($actionLinks as $item)
                        <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 font-semibold text-pink-300 hover:bg-white/10">
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    @foreach($mainNavLinks as $item)
                        @if(!empty($item['is_logout']))
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="button" class="block w-full rounded-lg px-3 py-2 text-left text-gray-200 hover:bg-white/10" @click="confirmSignOut($el.closest('form'))">
                                    Sign Out
                                </button>
                            </form>
                        @elseif(strtolower($item['label']) === 'escorts')
                            <div x-data="{ open: false, search: '', links: {{ \Illuminate\Support\Js::from($escortMenuLinks->all()) }}, get filteredLinks() { const term = this.search.toLowerCase().trim(); return term ? this.links.filter((link) => link.search.includes(term)) : this.links; } }">
                                <button @click="open = !open" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">
                                    <span>{{ $item['label'] }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                </button>

                                <div x-cloak x-show="open" x-transition class="ml-3 mt-1 space-y-0.5 border-l border-white/10 pl-3">
                                    <div class="pr-3 pt-2">
                                        <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-[#070e22] px-3 py-2">
                                            <i class="fa-solid fa-magnifying-glass text-xs text-gray-400"></i>
                                            <input x-model.live.debounce.150ms="search" type="text" placeholder="Search escorts menu" class="w-full border-0 bg-transparent text-sm text-white placeholder:text-gray-400 focus:outline-none focus:ring-0">
                                        </div>
                                    </div>

                                    <template x-for="link in filteredLinks" :key="`${link.label}-${link.url}`">
                                        <a @click="mobileMenu = false; open = false; search = ''" :href="link.url" class="block rounded-lg px-3 py-2 text-gray-300 hover:bg-white/10">
                                            <span x-text="link.label"></span>
                                        </a>
                                    </template>

                                    <p x-show="filteredLinks.length === 0" class="rounded-lg px-3 py-2 text-sm text-gray-400">
                                        No matching escorts found.
                                    </p>
                                </div>
                            </div>
                        @else
                            @php $isActive = $isNavItemActive((string) $item['url']); @endphp

                            <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 {{ $isActive ? 'bg-white/10 font-medium text-white' : 'text-gray-200 hover:bg-white/10' }}">
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach

                    @auth
                        <div class="border-t border-white/10 px-3 pt-4">
                            <p class="text-xs uppercase tracking-[0.24em] text-gray-400">
                                Account
                            </p>

                            @foreach($authMenuGroups as $group)
                                @if(filled($group['title']))
                                    <p class="mt-3 px-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">
                                        {{ $group['title'] }}
                                    </p>
                                @endif

                                @foreach($group['items'] as $item)
                                    <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    @endauth

                    <a @click="mobileMenu = false" href="{{ route('favourites') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('favourites') ? 'bg-white/10 font-medium text-pink-400' : 'text-gray-200 hover:bg-white/10' }}">
                        <i class="fa-solid fa-heart mr-1.5 text-xs text-pink-500"></i>
                        Favourites
                    </a>

                    @if($showFreeTrialCta && filled($freeTrialCtaText) && filled($freeTrialCtaUrl))
                        <a @click="mobileMenu = false" href="{{ $freeTrialCtaUrl }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">
                            {{ $freeTrialCtaText }}
                        </a>
                    @endif

                    @foreach($mobileExtraLinks as $item)
                        <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</header>
