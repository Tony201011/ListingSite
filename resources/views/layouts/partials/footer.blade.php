<footer class="border-t border-gray-800 bg-gray-950 px-4 pt-10 pb-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 rounded-2xl border border-pink-500/20 bg-gradient-to-r from-gray-900 to-gray-900/60 p-5 sm:flex sm:items-center sm:justify-between sm:p-6">
            <div>
                <h3 class="text-base font-semibold text-white sm:text-lg">Want more visibility and calls?</h3>
                <p class="mt-1 text-sm text-gray-400">Promote your profile with VIP and Diamond plans to reach more users in high-traffic placements.</p>
            </div>
            <div class="mt-4 flex gap-2 sm:mt-0">
                <a href="{{ url('/membership') }}" class="inline-flex rounded-lg border border-pink-500 px-4 py-2 text-sm font-semibold text-pink-400 transition hover:bg-pink-500/10">View Plans</a>
                <a href="{{ url('/signup') }}" class="inline-flex rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">Create Listing</a>
            </div>
        </div>

        <div class="grid gap-8 text-sm md:grid-cols-2 lg:grid-cols-4">
            <div>
                <span class="text-xl font-bold text-white">HOT<span class="text-pink-500">ESCORTS</span></span>
                <p class="mt-4 leading-relaxed text-gray-500">Australia’s independent adult directory for discovery, profile promotion, and secure advertiser management.</p>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                    <span class="rounded-full border border-gray-700 px-2 py-1">18+ Adults Only</span>
                    <span class="rounded-full border border-gray-700 px-2 py-1">Verified Listings</span>
                    <span class="rounded-full border border-gray-700 px-2 py-1">Privacy First</span>
                </div>
            </div>

            <div>
                <h4 class="mb-4 font-semibold uppercase tracking-wider text-white">Navigation</h4>
                <ul class="space-y-2 text-gray-500">
                    <li><a href="{{ url('/') }}" class="transition hover:text-pink-400">Home</a></li>
                    <li><a href="#" class="transition hover:text-pink-400">Escorts</a></li>
                    <li><a href="#" class="transition hover:text-pink-400">Naughty corner</a></li>
                    <li><a href="#" class="transition hover:text-pink-400">Blog</a></li>
                    <li><a href="#" class="transition hover:text-pink-400">Locations</a></li>
                    <li><a href="#" class="transition hover:text-pink-400">BDSM</a></li>
                    <li><a href="#" class="transition hover:text-pink-400">Escort reviews</a></li>
                    <li><a href="#" class="transition hover:text-pink-400">Escort announcements</a></li>
                </ul>
            </div>

            <div>
                <h4 class="mb-4 font-semibold uppercase tracking-wider text-white">Advertisers</h4>
                <ul class="space-y-2 text-gray-500">
                    <li><a href="{{ url('/signup') }}" class="transition hover:text-pink-400">Create Profile</a></li>
                    <li><a href="{{ url('/signin') }}" class="transition hover:text-pink-400">Provider Login</a></li>
                    <li><a href="{{ url('/membership') }}" class="transition hover:text-pink-400">Membership Plans</a></li>
                    <li><a href="{{ route('refund-policy') }}" class="transition hover:text-pink-400">Pricing & Refunds</a></li>
                </ul>
            </div>

            <div>
                <h4 class="mb-4 font-semibold uppercase tracking-wider text-white">Legal & Help</h4>
                <ul class="space-y-2 text-gray-500">
                    <li><a href="{{ route('faq') }}" class="transition hover:text-pink-400">FAQ</a></li>
                    <li><a href="{{ route('contact-us') }}" class="transition hover:text-pink-400">Contact Us</a></li>
                    <li><a href="{{ route('terms-and-conditions') }}" class="transition hover:text-pink-400">Terms & Conditions</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="transition hover:text-pink-400">Privacy Policy</a></li>
                    <li><a href="{{ route('anti-spam-policy') }}" class="transition hover:text-pink-400">Anti-Spam Policy</a></li>
                </ul>

                <div class="mt-5 flex items-center gap-2 text-gray-400">
                    <a href="{{ route('contact-us') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-700 transition hover:border-pink-500 hover:text-pink-400"><i class="fa-brands fa-instagram"></i></a>
                    <a href="{{ route('contact-us') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-700 transition hover:border-pink-500 hover:text-pink-400"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="{{ route('contact-us') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-700 transition hover:border-pink-500 hover:text-pink-400"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-800 pt-5 text-xs text-gray-500 sm:flex sm:items-center sm:justify-between">
            <p>© 2026 Hotescorts Directory. All rights reserved.</p>
            <p class="mt-2 sm:mt-0">This platform is for adults only (18+) and provides advertising listings only.</p>
        </div>
    </div>
</footer>
