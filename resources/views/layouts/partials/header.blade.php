<nav class="bg-gray-900/95 border-b border-gray-800 sticky top-0 z-50 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex-shrink-0 flex items-center">
                <span class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent">
                    HOT<span class="text-white">ESCORTS</span>
                </span>
            </div>

            <div class="hidden md:flex space-x-8 text-sm font-medium">
                @foreach(main_menu_items() as $menu)
                    <a href="{{ $menu->url ?? '#' }}" class="hover:text-purple-400 transition flex items-center gap-1">
                        {{ $menu->label }}
                        @if($menu->is_new)
                            <span class="bg-purple-400 text-white text-[10px] px-2 py-0.5 rounded ml-1">NEW</span>
                        @endif
                        @if($menu->icon)
                            <i class="{{ $menu->icon }} ml-1"></i>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- <div class="hidden md:flex items-center space-x-4">
                <button @click="loginModal = true" class="text-sm border border-gray-700 px-4 py-2 rounded-lg hover:bg-gray-800">Login</button>
                <button @click="registerModal = true" class="text-sm bg-purple-600 px-4 py-2 rounded-lg hover:bg-purple-700 font-bold transition">Join Now</button>
            </div> --}}

<div class="hidden md:flex items-center space-x-4">
    @auth
        <!-- User is logged in (includes Filament admin users) -->
        <div class="flex items-center space-x-4">
            <!-- Dashboard button - links to Filament admin -->
            <a href="{{ filament()->getUrl() }}" class="text-sm bg-purple-600 px-4 py-2 rounded-lg hover:bg-purple-700 font-bold transition">
                Dashboard
            </a>

            {{-- <!-- Optional: Show admin indicator if user is filament admin -->
            @if(auth()->user()->can('access filament'))
                <span class="text-xs bg-green-600 text-white px-2 py-1 rounded">Admin</span>
            @endif

            <!-- User info -->
            <span class="text-sm text-gray-300">{{ auth()->user()->name }}</span> --}}

            <!-- Logout form using Filament's logout route -->
            {{-- <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                @csrf
                <button type="submit" class="text-sm border border-gray-700 px-4 py-2 rounded-lg hover:bg-gray-800">\
                    <span class="text-xs bg-green-600 text-white px-2 py-1 rounded">Logout</span>

                </button>
            </form> --}}
        </div>
    @else
        <!-- User is not logged in -->
        <button @click="loginModal = true" class="text-sm border border-gray-700 px-4 py-2 rounded-lg hover:bg-gray-800">
            Login
        </button>
        <button @click="registerModal = true" class="text-sm bg-purple-600 px-4 py-2 rounded-lg hover:bg-purple-700 font-bold transition">
            Join Now
        </button>
    @endauth
</div>

            <div class="md:hidden flex items-center">
                <button @click="mobileMenu = !mobileMenu" class="text-gray-400 hover:text-white">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <div x-cloak x-show="mobileMenu" x-transition class="md:hidden pb-4 border-t border-gray-800">
            <div class="pt-4 space-y-2 text-sm">
                <a @click="mobileMenu = false" href="{{ url('/provider/content-listings') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">Browse All</a>
                <a @click="mobileMenu = false" href="{{ url('/provider/content-listings') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">Verified</a>
                <a @click="mobileMenu = false" href="{{ route('faq') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">Locations</a>
                <a @click="mobileMenu = false" href="{{ url('/provider/login') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">Provider Login</a>
                <a @click="mobileMenu = false" href="{{ url('/provider/register') }}" class="block px-3 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 font-bold text-center mt-2">Join Now</a>
            </div>
        </div>
    </div>
</nav>

<!-- Login Modal -->
<div x-cloak x-show="loginModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
    <div class="bg-gray-900 rounded-2xl shadow-xl w-full max-w-md p-8 relative">
        <button @click="loginModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
        <h2 class="text-2xl font-bold text-white mb-6 text-center">Login</h2>
        <div x-data="{ tab: 'provider' }">
            <div class="flex justify-center gap-4 mb-6">
                <button @click="tab = 'provider'" :class="tab === 'provider' ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-400'" class="px-4 py-2 rounded-lg font-semibold transition">Provider Login</button>
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
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-lg">Login as Provider</button>
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
                <button @click="tab = 'provider'" :class="tab === 'provider' ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-400'" class="px-4 py-2 rounded-lg font-semibold transition">Provider Register</button>
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
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-lg mt-4">Sign up</button>
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
