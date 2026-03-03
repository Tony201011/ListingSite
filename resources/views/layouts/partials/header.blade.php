<header class="sticky top-0 z-50 border-b border-gray-800 bg-gray-900/95 backdrop-blur-md">
    <!-- Top bar (with Follow Alice) -->
    <div class="hidden border-b border-gray-800 bg-gray-950 lg:block">
        <div class="mx-auto flex h-10 max-w-7xl items-center justify-between px-4 text-xs text-gray-400 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-shield-heart text-pink-500"></i> Verified advertisers</span>
                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-location-dot text-pink-500"></i> Australia-wide directory</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="#" class="transition hover:text-pink-400">Follow Alice</a>  <!-- from first image -->
                <a href="{{ route('faq') }}" class="transition hover:text-pink-400">Help</a>
                <a href="{{ route('contact-us') }}" class="transition hover:text-pink-400">Contact</a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Main row: logo, search, actions, auth -->
        <div class="flex min-h-[70px] items-center justify-between gap-4 py-3 md:hidden">
            <!-- Logo (escortify) -->
            <a href="{{ url('/') }}" class="shrink-0">
                <span class="text-xl font-bold text-white">HOT<span class="text-pink-500">ESCORTS</span></span>
            </a>

            <!-- Search (unchanged) -->
            <form action="{{ url('/provider/content-listings') }}" method="GET" class="hidden flex-1 xl:block">
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
                <a href="#" class="text-sm font-medium text-gray-300 transition hover:text-pink-400">Pricing</a>
                <a href="#" class="text-sm font-medium text-gray-300 transition hover:text-pink-400">Diamonds</a>
                <a href="#" class="text-sm font-medium text-gray-300 transition hover:text-pink-400">Superboost</a>
                <a href="#" class="text-sm font-medium text-gray-300 transition hover:text-pink-400">Add advertisement</a>

                @auth
                    <!-- User dropdown (My profile / Logoff) -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm font-medium text-gray-100 transition hover:bg-gray-700">
                            <i class="fa-solid fa-user text-pink-500"></i>
                            <span>Account</span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg border border-gray-700 bg-gray-800 py-1 shadow-lg">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">My profile</a>
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
                <span class="text-xl font-bold text-white">HOT<span class="text-pink-500">ESCORTS</span></span>
            </a>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">Home</a>
            <a href="#" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">Escorts</a>
            <a href="#" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">Naughty corner</a>
            <a href="#" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">Blog</a>
            <a href="#" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">Locations</a>
            <a href="#" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">BDSM</a>
            <a href="#" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">Escort reviews</a>
            <a href="#" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">Escort announcements</a>
            <a href="{{ url('/signin') }}" class="ml-auto inline-flex items-center rounded-md border border-gray-700 px-3 py-1.5 text-sm font-medium text-gray-200 transition hover:bg-gray-800 hover:text-white">Login</a>
        </div>

        <!-- Mobile menu (updated with all items) -->
        <div x-cloak x-show="mobileMenu" x-transition class="space-y-3 border-t border-gray-800 py-4 md:hidden">
            <div class="space-y-1 text-sm">
                <!-- Main nav links -->
                <a @click="mobileMenu = false" href="{{ url('/') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Home</a>
                <a @click="mobileMenu = false" href="#" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Escorts</a>
                <a @click="mobileMenu = false" href="#" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Naughty corner</a>
                <a @click="mobileMenu = false" href="#" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Blog</a>
                <a @click="mobileMenu = false" href="#" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Locations</a>
                <a @click="mobileMenu = false" href="#" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">BDSM</a>
                <a @click="mobileMenu = false" href="#" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Escort reviews</a>
                <a @click="mobileMenu = false" href="#" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Escort announcements</a>
                <a @click="mobileMenu = false" href="{{ url('/signin') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Login</a>
                <!-- Static contact (optional) -->
                <a @click="mobileMenu = false" href="{{ route('contact-us') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Contact</a>
            </div>
        </div>
    </div>
</header>


