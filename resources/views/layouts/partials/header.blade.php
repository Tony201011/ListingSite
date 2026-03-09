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
            ['label' => 'Diamonds', 'url' => url('/purchase-credit')],
            ['label' => 'Superboost', 'url' => url('/purchase-credit')],
            ['label' => 'Add advertisement', 'url' => url('/signup')],
        ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

        $mainNavLinks = collect($headerWidget?->main_nav_links ?? [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'About us', 'url' => route('about-us')],
            ['label' => 'Pricing', 'url' => url('/pricing')],
            ['label' => 'Escorts', 'url' => url('/')],
            ['label' => 'Naughty corner', 'url' => route('naughty-corner')],
            ['label' => 'Blog', 'url' => route('blog')],
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

        $mobileExtraLinks = collect($headerWidget?->mobile_extra_links ?? [
            ['label' => 'Contact', 'url' => route('contact-us')],
        ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

        $showTopBar = $headerWidget?->enable_top_bar ?? true;
        $showSearch = $headerWidget?->enable_search ?? true;
@endphp

<header class="sticky top-0 z-50 border-b border-gray-800 bg-gray-900/95 backdrop-blur-md" style="{{ $headerStyle }}">

    <!-- Top bar (with Follow Alice) -->
    @if($showTopBar)
        <div class="hidden border-b border-gray-800 bg-gray-950 lg:block">
            <div class="mx-auto flex h-10 max-w-7xl items-center justify-between px-4 text-xs text-gray-400 sm:px-6 lg:px-8">
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
                <div class="flex items-center gap-4">
                    @foreach($topRightLinks as $item)
                        <a href="{{ $item['url'] }}" class="transition hover:text-pink-400">{{ $item['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Main row: logo, search, actions, auth -->
        <div class="flex min-h-[70px] items-center justify-between gap-4 py-3 md:hidden">
            <!-- Logo (escortify) -->
            <a href="{{ url('/') }}" class="shrink-0">
                @if($logoType === 'image' && filled($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Site Logo" class="h-auto w-auto" style="{{ $logoStyle }}">
                @else
                    <span class="text-xl font-bold text-white">{{ $brandPrimary }}<span class="text-pink-500">{{ $brandAccent }}</span></span>
                @endif
            </a>

            <!-- Search (unchanged) -->
            <form action="{{ url('/provider/content-listings') }}" method="GET" class="{{ $showSearch ? 'hidden flex-1 xl:block' : 'hidden' }}">
                <div class="mx-auto flex max-w-2xl items-center rounded-xl border border-gray-700 bg-gray-800/80 p-1.5">
                    <div class="flex min-w-0 flex-1 items-center gap-2 px-2">
                        <i class="fa-solid fa-magnifying-glass text-gray-500"></i>
                        <input type="text" name="q" placeholder="Search by name or keyword" class="w-full border-0 bg-transparent text-sm text-white placeholder:text-gray-500 focus:outline-none focus:ring-0">
                    </div>
                    <div class="hidden items-center gap-2 border-l border-gray-700 px-3 lg:flex">
                        <i class="fa-solid fa-location-dot text-gray-500"></i>
                        <input type="text" name="city" placeholder="City" class="w-28 border-0 bg-transparent text-sm text-white placeholder:text-gray-500 focus:outline-none focus:ring-0">
                    </div>
                    <button type="submit" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">Search</button>
                </div>
            </form>

            <!-- Action links & Auth (from third image) -->
            <div class="hidden items-center space-x-4 md:flex">
                <!-- Action links -->
                @foreach($actionLinks as $item)
                    <a href="{{ $item['url'] }}" class="text-sm font-medium text-gray-300 transition hover:text-pink-400">{{ $item['label'] }}</a>
                @endforeach

                @auth
                    <!-- User dropdown (My profile / Logoff) -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm font-medium text-gray-100 transition hover:bg-gray-700">
                            <i class="fa-solid fa-user text-pink-500"></i>
                            <span>Account</span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg border border-gray-700 bg-gray-800 py-1 shadow-lg">
                            <a href="{{ url('/view-profile-setting') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">My profile</a>
                            <a href="{{ filament()->getUrl() }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white">Logoff</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ url('/signin') }}" class="rounded-lg border border-gray-700 px-4 py-2 text-sm font-medium text-gray-100 transition hover:bg-gray-800">Login</a>
                    <a href="{{ url('/signup') }}" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">Join Now</a>
                @endauth
            </div>

            <!-- Mobile menu button (unchanged) -->
            <button @click="mobileMenu = !mobileMenu" class="md:hidden text-gray-300 hover:text-white" aria-label="Toggle menu">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Second row: main navigation (from first & second images) -->
        <div class="hidden items-center gap-1 border-t border-gray-800 py-3 md:flex">
            <a href="{{ url('/') }}" class="mr-4 shrink-0">
                @if($logoType === 'image' && filled($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Site Logo" class="h-auto w-auto" style="{{ $logoStyle }}">
                @else
                    <span class="text-xl font-bold text-white">{{ $brandPrimary }}<span class="text-pink-500">{{ $brandAccent }}</span></span>
                @endif
            </a>
            @foreach($mainNavLinks as $item)
                @if(strtolower($item['label']) === 'escorts')
                    <div class="relative group">
                        <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">
                            {{ $item['label'] }}
                            <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
                        </a>
                        <div class="absolute left-0 mt-2 w-64 rounded-lg bg-gray-800 py-2 shadow-lg z-50 opacity-0 group-hover:opacity-100 group-hover:visible invisible transition-opacity duration-200">
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Brisbane escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Sydney escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Melbourne escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Adelaide escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Canberra escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Perth escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Darwin escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Gold Coast escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Sunshine Coast escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Newcastle escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Cairns escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Tasmania escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Touring escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Escorts directory</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Search for escorts</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">Escorts near me</a>
                            <a href="#" class="block px-5 py-2 text-gray-200 hover:bg-gray-700">View all our escorts</a>
                        </div>
                    </div>
                @else
                    <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">{{ $item['label'] }}</a>
                @endif
            @endforeach
            <a href="{{ url('/signin') }}" class="ml-auto inline-flex items-center rounded-md border border-gray-700 px-3 py-1.5 text-sm font-medium text-gray-200 transition hover:bg-gray-800 hover:text-white">Login</a>
        </div>

        <!-- Mobile menu (updated with all items) -->
        <div x-cloak x-show="mobileMenu" x-transition class="space-y-3 border-t border-gray-800 py-4 md:hidden">
            <div class="space-y-1 text-sm">
                <!-- Main nav links -->
                @foreach($mainNavLinks as $item)
                    <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">{{ $item['label'] }}</a>
                @endforeach
                <a @click="mobileMenu = false" href="{{ url('/signin') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Login</a>
                @foreach($mobileExtraLinks as $item)
                    <a @click="mobileMenu = false" href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">{{ $item['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
</header>


