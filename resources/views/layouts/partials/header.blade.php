<header class="sticky top-0 z-50 border-b border-gray-800 bg-gray-900/95 backdrop-blur-md">
    <div class="hidden border-b border-gray-800 bg-gray-950 lg:block">
        <div class="mx-auto flex h-10 max-w-7xl items-center justify-between px-4 text-xs text-gray-400 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-shield-heart text-pink-500"></i> Verified advertisers</span>
                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-location-dot text-pink-500"></i> Australia-wide directory</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('faq') }}" class="transition hover:text-pink-400">Help</a>
                <a href="{{ route('contact-us') }}" class="transition hover:text-pink-400">Contact</a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[70px] items-center justify-between gap-4 py-3">
            <a href="{{ url('/') }}" class="shrink-0">
                <span class="text-2xl font-bold leading-none text-white">HOT<span class="text-pink-500">ESCORTS</span></span>
            </a>

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

            <div class="hidden items-center space-x-3 md:flex">
                @auth
                    <a href="{{ filament()->getUrl() }}" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">Dashboard</a>
                @else
                    <button @click="loginModal = true" class="rounded-lg border border-gray-700 px-4 py-2 text-sm font-medium text-gray-100 transition hover:bg-gray-800">Login</button>
                    <button @click="registerModal = true" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">Join Now</button>
                @endauth
            </div>

            <button @click="mobileMenu = !mobileMenu" class="md:hidden text-gray-300 hover:text-white" aria-label="Toggle menu">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>

        <div class="hidden items-center gap-1 border-t border-gray-800 py-3 md:flex">
            @foreach(main_menu_items() as $menu)
                <a href="{{ $menu->url ?? '#' }}" class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">
                    {{ $menu->label }}
                    @if($menu->is_new)
                        <span class="rounded bg-pink-600 px-1.5 py-0.5 text-[10px] font-bold text-white">NEW</span>
                    @endif
                    @if($menu->icon)
                        <i class="{{ $menu->icon }} text-xs"></i>
                    @endif
                </a>
            @endforeach
        </div>

        <div x-cloak x-show="mobileMenu" x-transition class="space-y-3 border-t border-gray-800 py-4 md:hidden">
            <form action="{{ url('/provider/content-listings') }}" method="GET" class="space-y-2">
                <input type="text" name="q" placeholder="Search by name or keyword" class="h-10 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 text-sm text-white placeholder:text-gray-500 focus:border-pink-500 focus:outline-none">
                <input type="text" name="city" placeholder="City" class="h-10 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 text-sm text-white placeholder:text-gray-500 focus:border-pink-500 focus:outline-none">
                <button type="submit" class="w-full rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white">Search</button>
            </form>

            <div class="space-y-1 text-sm">
                @foreach(main_menu_items() as $menu)
                    <a @click="mobileMenu = false" href="{{ $menu->url ?? '#' }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">{{ $menu->label }}</a>
                @endforeach
                <a @click="mobileMenu = false" href="{{ route('contact-us') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-gray-800">Contact</a>
            </div>

            @guest
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button @click="loginModal = true; mobileMenu = false" class="rounded-lg border border-gray-700 px-3 py-2 text-sm font-medium text-gray-100">Login</button>
                    <button @click="registerModal = true; mobileMenu = false" class="rounded-lg bg-pink-600 px-3 py-2 text-sm font-semibold text-white">Join Now</button>
                </div>
            @endguest
        </div>
    </div>
</header>

<!-- Login Modal -->
<div x-cloak x-show="loginModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
    <div class="bg-gray-900 rounded-2xl shadow-xl w-full max-w-md p-8 relative">
        <button @click="loginModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
        <h2 class="text-2xl font-bold text-white mb-6 text-center">Login</h2>
        <div x-data="{ tab: 'provider' }">
            <div class="flex justify-center gap-4 mb-6">
                <button @click="tab = 'provider'" :class="tab === 'provider' ? 'bg-pink-600 text-white' : 'bg-gray-800 text-gray-400'" class="px-4 py-2 rounded-lg font-semibold transition">Provider Login</button>
                <button @click="tab = 'user'" :class="tab === 'user' ? 'bg-pink-500 text-white' : 'bg-gray-800 text-gray-400'" class="px-4 py-2 rounded-lg font-semibold transition">User Login</button>
            </div>
            <div x-show="tab === 'provider'">
                <form method="POST" action="{{ url('/provider/login') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-400 mb-2">Email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-400 mb-2">Password</label>
                        <input type="password" name="password" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                    </div>
                    <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 rounded-lg">Login as Provider</button>
                </form>
            </div>
            <div x-show="tab === 'user'">
                <form method="POST" action="{{ url('/user/login') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-400 mb-2">Email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-400 mb-2">Password</label>
                        <input type="password" name="password" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                    </div>
                    <button type="submit" class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 rounded-lg">Login as User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div x-cloak x-show="registerModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
    <div class="bg-gray-900 rounded-2xl shadow-xl w-full max-w-md p-8 relative overflow-y-auto max-h-[90vh]">
        <button @click="registerModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
        <h2 class="text-2xl font-bold text-white mb-6 text-center">Register</h2>
        <div x-data="{ tab: 'provider' }">
            <div class="flex justify-center gap-4 mb-6">
                <button @click="tab = 'provider'" :class="tab === 'provider' ? 'bg-pink-600 text-white' : 'bg-gray-800 text-gray-400'" class="px-4 py-2 rounded-lg font-semibold transition">Provider Register</button>
                <button @click="tab = 'user'" :class="tab === 'user' ? 'bg-pink-500 text-white' : 'bg-gray-800 text-gray-400'" class="px-4 py-2 rounded-lg font-semibold transition">User Register</button>
            </div>
            <div x-show="tab === 'provider'">
                <form method="POST" action="{{ url('/provider/register') }}">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-400 mb-2">Name*</label>
                            <input type="text" name="name" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Email address*</label>
                            <input type="email" name="email" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Password*</label>
                            <input type="password" name="password" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Confirm password*</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Provider Name*</label>
                            <input type="text" name="provider_name" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Slug</label>
                            <input type="text" name="slug" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Age</label>
                            <input type="number" name="age" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-gray-400 mb-2">Description</label>
                            <textarea name="description" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Country</label>
                            <select name="country" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                                <option value="">Select an option</option>
                                <!-- Add country options -->
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">State</label>
                            <select name="state" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                                <option value="">Select an option</option>
                                <!-- Add state options -->
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">City</label>
                            <input type="text" name="city" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Latitude</label>
                            <input type="text" name="latitude" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Longitude</label>
                            <input type="text" name="longitude" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Phone</label>
                            <input type="text" name="phone" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Whatsapp</label>
                            <input type="text" name="whatsapp" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div class="flex items-center mt-6">
                            <input type="checkbox" name="verified" id="verified" class="mr-2">
                            <label for="verified" class="text-gray-400">Verified</label>
                        </div>
                        <div class="flex items-center mt-6">
                            <input type="checkbox" name="featured" id="featured" class="mr-2">
                            <label for="featured" class="text-gray-400">Featured</label>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Membership ID</label>
                            <input type="text" name="membership_id" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Profile Status*</label>
                            <select name="profile_status" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Expires At</label>
                            <input type="date" name="expires_at" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 rounded-lg mt-4">Sign up</button>
                </form>
            </div>
            <div x-show="tab === 'user'">
                <form method="POST" action="{{ url('/user/register') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-400 mb-2">Email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 mb-2">Password</label>
                        <input type="password" name="password" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-400 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2 rounded-lg bg-gray-800 text-white focus:outline-none" required>
                    </div>
                    <button type="submit" class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 rounded-lg">Register as User</button>
                </form>
            </div>
        </div>
    </div>
</div>
