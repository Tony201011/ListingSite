<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    {{-- Page Header Box --}}
    <div class="mb-8 rounded-xl border border-gray-700 bg-gradient-to-r from-gray-800/80 to-gray-900/80 p-6 shadow-lg">
        <h1 class="text-3xl font-bold tracking-tight text-white">{{ $title }}</h1>
        @if(!empty($subtitle))
            <p class="mt-2 text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>

    @if (!empty($faqs) && $faqs->isNotEmpty())
        {{-- FAQ Section Box --}}
        <div class="rounded-xl border border-gray-700 bg-gray-800/30 p-6 shadow-xl">
            <div class="mb-6 flex items-center justify-between border-b border-gray-700 pb-4">
                <h2 class="text-2xl font-semibold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Frequently Asked Questions
                </h2>
                <span class="rounded-full bg-blue-600/20 px-3 py-1 text-sm text-blue-400">{{ $faqs->count() }} Questions</span>
            </div>

            <div class="space-y-4" x-data="{ activeIndex: null }">
                @foreach ($faqs as $index => $faq)
                    <div class="rounded-lg border border-gray-700 bg-gray-800/50 overflow-hidden transition-all duration-200 hover:border-gray-600">
                        <h2
                            @click="activeIndex = activeIndex === {{ $index }} ? null : {{ $index }}"
                            class="text-lg font-semibold text-white cursor-pointer select-none hover:bg-gray-700/50 p-5 transition-colors"
                            :class="{ 'bg-gray-700/50': activeIndex === {{ $index }} }"
                        >
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-3">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600/20 text-sm text-blue-400">
                                        {{ $index + 1 }}
                                    </span>
                                    {{ $faq->question }}
                                </span>
                                <span class="text-gray-400">
                                    <svg x-show="activeIndex !== {{ $index }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <svg x-show="activeIndex === {{ $index }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </h2>

                        <article
                            x-show="activeIndex === {{ $index }}"
                            x-collapse
                            class="max-w-none text-gray-200 leading-relaxed p-5 pt-0 border-t border-gray-700"
                        >
                            <div class="prose prose-invert max-w-none">
                                {!! $faq->answer !!}
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            {{-- FAQ Footer --}}
            <div class="mt-6 rounded-lg bg-gray-800/50 p-4 text-center border border-gray-700">
                <p class="text-gray-400">Still have questions? <a href="#" class="text-blue-400 hover:text-blue-300 transition-colors">Contact our support team</a></p>
            </div>
        </div>

    @elseif (!empty($content))
        {{-- Content Section Box --}}
        <div class="rounded-xl border border-gray-700 bg-gray-800/30 p-6 shadow-xl">
            <div class="mb-6 flex items-center justify-between border-b border-gray-700 pb-4">
                <h2 class="text-2xl font-semibold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    Content
                </h2>
                @if(isset($updatedAt))
                    <span class="text-sm text-gray-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Last updated: {{ $updatedAt->format('M d, Y') }}
                    </span>
                @endif
            </div>

            <article class="prose prose-invert max-w-none text-gray-200 leading-relaxed">
                {!! $content !!}
            </article>
        </div>

    @elseif (!empty($contact))
        {{-- Contact Section Box --}}
        <div class="rounded-xl border border-gray-700 bg-gray-800/30 p-6 shadow-xl">
            <div class="mb-6 flex items-center gap-3 border-b border-gray-700 pb-4">
                <div class="rounded-full bg-blue-600/20 p-3">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-white">Get in Touch</h2>
                    <p class="text-sm text-gray-400">We'd love to hear from you</p>
                </div>
            </div>

            {{-- Two Column Layout --}}
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                {{-- Left Column: Map & Contact Info Boxes --}}
                <div class="space-y-6">
                    {{-- Map Box --}}
                    <div class="rounded-xl border border-gray-700 bg-gray-800/50 p-6 hover:border-gray-600 transition-all duration-300">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Our Location
                        </h3>
                        <div class="aspect-video w-full overflow-hidden rounded-lg bg-gray-700 shadow-inner">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d211943.404981566!2d151.05450589999998!3d-33.87271885!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b12ae2b1212c2c7%3A0x4c6c462b2e2f7f6a!2sSydney%20NSW%2C%20Australia!5e0!3m2!1sen!2sus!4v1700000000000!5m2!1sen!2sus"
                                class="w-full h-full"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>

                    {{-- Contact Info Box --}}
                    <div class="rounded-xl border border-gray-700 bg-gray-800/50 p-6 hover:border-gray-600 transition-all duration-300">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                            Contact Information
                        </h3>

                        <div class="space-y-4 text-gray-300">
                            <div class="flex items-start gap-3 group hover:bg-gray-700/30 p-2 rounded-lg transition-colors">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <a href="mailto:contact@example.com" class="text-gray-300 hover:text-blue-400 transition-colors">
                                        contact@example.com
                                    </a>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 group hover:bg-gray-700/30 p-2 rounded-lg transition-colors">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Phone</p>
                                    <a href="tel:+15551234567" class="text-gray-300 hover:text-blue-400 transition-colors">
                                        +1 (555) 123-4567
                                    </a>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 group hover:bg-gray-700/30 p-2 rounded-lg transition-colors">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Address</p>
                                    <address class="not-italic text-gray-300">
                                        123 Main St, Sydney<br>
                                        NSW 2000, Australia
                                    </address>
                                </div>
                            </div>
                        </div>

                        {{-- Business Hours Box --}}
                        <div class="mt-6 pt-4 border-t border-gray-700">
                            <p class="font-medium text-white mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Business Hours
                            </p>
                            <ul class="space-y-2 text-sm">
                                <li class="flex justify-between items-center p-2 rounded bg-gray-700/20">
                                    <span class="text-gray-400">Monday - Friday:</span>
                                    <span class="text-white font-medium">9:00 AM - 6:00 PM</span>
                                </li>
                                <li class="flex justify-between items-center p-2 rounded bg-gray-700/20">
                                    <span class="text-gray-400">Saturday:</span>
                                    <span class="text-white font-medium">10:00 AM - 4:00 PM</span>
                                </li>
                                <li class="flex justify-between items-center p-2 rounded bg-gray-700/20">
                                    <span class="text-gray-400">Sunday:</span>
                                    <span class="text-red-400 font-medium">Closed</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Contact Form Box --}}
                <div class="rounded-xl border border-gray-700 bg-gray-800/50 p-6 hover:border-gray-600 transition-all duration-300">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="rounded-full bg-green-600/20 p-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Send us a message</h3>
                    </div>

                    <form action="#" method="POST" class="space-y-5">
                        @csrf

                        {{-- Name Field --}}
                        <div class="group">
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-1 group-focus-within:text-blue-400 transition-colors">
                                Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                value="{{ old('name') }}"
                                required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email Field --}}
                        <div class="group">
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-1 group-focus-within:text-blue-400 transition-colors">
                                Email <span class="text-red-400">*</span>
                            </label>
                            <input type="email" name="email" id="email"
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                value="{{ old('email') }}"
                                required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Subject Field --}}
                        <div class="group">
                            <label for="subject" class="block text-sm font-medium text-gray-300 mb-1 group-focus-within:text-blue-400 transition-colors">
                                Subject <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="subject" id="subject"
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                value="{{ old('subject') }}"
                                required>
                            @error('subject')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Message Field --}}
                        <div class="group">
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-1 group-focus-within:text-blue-400 transition-colors">
                                Message <span class="text-red-400">*</span>
                            </label>
                            <textarea name="message" id="message" rows="5"
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                required>{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center justify-between pt-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 py-2.5 px-6 text-sm font-medium text-white shadow-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 transform hover:scale-105">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Send Message
                            </button>
                            <p class="text-xs text-gray-500">* Required fields</p>
                        </div>
                    </form>

                    {{-- Success Message Box --}}
                    @if (session('success'))
                        <div class="mt-6 rounded-lg border border-green-700 bg-green-600/20 p-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-green-400">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @else
        {{-- Empty State Box --}}
        <div class="rounded-xl border border-gray-700 bg-gray-800/30 p-12 text-center shadow-xl">
            <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-white">No Content Available</h3>
            <p class="mt-2 text-gray-400">{{ $emptyMessage ?? 'Check back later for updates.' }}</p>
        </div>
    @endif

</main>
