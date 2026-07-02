@extends('layouts.frontend')

@push('styles')
    <style>
        /* Suppress the browser's built-in password-reveal icon to avoid duplicating
           the custom toggle button added by password-toggle.js */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        .signin-invalid-focus {
            outline: 2px solid rgba(220, 38, 38, 0.35);
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.2) !important;
        }

        .auth-consent-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            min-width: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: #fff;
            display: inline-block;
            vertical-align: top;
            margin-top: 0.125rem;
            transition: border-color 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
            cursor: pointer;
        }

        .auth-consent-checkbox:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(224, 78, 203, 0.25);
            border-color: #e04ecb;
        }

        .auth-consent-checkbox:checked {
            background-color: #e04ecb;
            border-color: #e04ecb;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 10.5l3.2 3.2L15 7' stroke='%23fff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 0.85rem 0.85rem;
        }
    </style>
@endpush

@section('content')
@include('auth.partials.recaptcha-responsive-assets')

<div class="bg-[#f8fafc] min-h-screen py-10 text-gray-900">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Login to your HOTESCORTS profile</h2>
        </div>

        <div class="mb-6 rounded-xl border border-pink-100 bg-pink-50 px-4 py-3 text-sm font-semibold text-gray-800">
            {{ $footerText?->adults_only_text ?? 'This website is intended for adults only.' }}
        </div>

        <div class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-md border border-gray-100">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 bg-[#e04ecb] rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6"></path>
                    </svg>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800">Welcome back</h3>
            </div>

            <p class="text-gray-600 mb-3 text-lg">Sign in to manage your profile, listings, and account settings.</p>
            <p class="text-xs leading-relaxed text-gray-700">
                Read:
                <a href="{{ route('age-and-consent-policy') }}" class="text-[#e04ecb] underline">Age & Consent Policy</a>,
                <a href="{{ route('content-moderation-policy') }}" class="text-[#e04ecb] underline">Content Moderation Policy</a>,
                <a href="{{ route('privacy-policy') }}" class="text-[#e04ecb] underline">Privacy Policy</a>,
                <a href="{{ route('terms-and-conditions') }}" class="text-[#e04ecb] underline">Terms & Conditions</a>.
            </p>
        </div>

        <div class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">
            <div id="js-flash-success" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700 text-sm hidden"></div>
            <script>
                (function () {
                    const msg = sessionStorage.getItem('flash_success');
                    if (msg) {
                        sessionStorage.removeItem('flash_success');
                        const el = document.getElementById('js-flash-success');
                        el.textContent = msg;
                        el.classList.remove('hidden');
                    }
                })();
            </script>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif



            @if (session('show_restore_account'))
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                    <p class="text-sm font-semibold">This account has been deleted and is currently within the restoration period.</p>
                    <p class="text-sm mt-1">You can restore your account instantly — no approval needed.</p>
                    <form method="POST" action="{{ route('account.restore.request') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                            Restore My Account
                        </button>
                    </form>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- <div class="mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                <p class="mb-3 text-sm font-semibold text-gray-700">Continue with</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('social.redirect', ['provider' => 'facebook']) }}" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#1877f2] hover:text-[#1877f2]">
                        <span class="text-base">f</span> Facebook
                    </a>
                    <a href="{{ route('social.redirect', ['provider' => 'twitter']) }}" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-slate-900 hover:text-slate-900">
                        <span class="text-base">𝕏</span> X / Twitter
                    </a>
                    <a href="{{ route('social.redirect', ['provider' => 'instagram']) }}" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#e4405f] hover:text-[#e4405f]">
                        <span class="text-base">◎</span> Instagram
                    </a>
                </div>
            </div> --}}

            <form
                x-data="signinForm()"
                @submit="submitForm"
                method="POST"
                action="{{ route('signin.submit') }}"
                novalidate
            >
                @csrf
                <input type="text" name="fake_username" autocomplete="username" tabindex="-1" class="hidden" aria-hidden="true">
                <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1" class="hidden" aria-hidden="true" data-no-toggle="true">

                <!-- Email -->
                <div class="mb-6" data-field-group>
                    <label for="signin_email" class="block font-semibold text-gray-800 mb-1">Email address <span class="text-red-600">*</span></label>
                    <input type="email" id="signin_email" x-ref="email" name="email" value="{{ old('email') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base" placeholder="Enter your email" required>
                    <div data-error-container="email">
                        @error('email')
                            <p class="text-red-600 text-sm mt-2" data-server-error="true" data-field="email">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-5" data-field-group>
                    <label for="signin_password" class="block font-semibold text-gray-800 mb-1">Password <span class="text-red-600">*</span></label>
                    <input type="password" id="signin_password" x-ref="password" name="password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base" placeholder="Enter your password" required>
                    <div data-error-container="password">
                        @error('password')
                            <p class="text-red-600 text-sm mt-2" data-server-error="true" data-field="password">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Keep me logged in (styled like the age confirmation pill) -->
                <div class="mb-6" data-field-group>
                    <label for="keep_logged_in" class="flex items-start gap-2.5 bg-gray-50 px-5 py-3 rounded-2xl cursor-pointer">
                        <input type="checkbox" id="keep_logged_in" x-ref="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="auth-consent-checkbox">
                        <span class="font-semibold text-gray-800">Keep me logged in on this device</span>
                    </label>
                    <div data-error-container="remember">
                        @error('remember')
                            <p class="text-red-600 text-sm mt-2" data-server-error="true" data-field="remember">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($shouldUseRecaptcha ?? false)
                    <div class="mb-8" data-field-group>
                        <div class="flex justify-center">
                            <div data-recaptcha-container x-ref="captcha" tabindex="-1" class="w-full max-w-full overflow-hidden">
                                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSetting->site_key ?? '' }}"></div>
                            </div>
                        </div>
                        <div data-error-container="captcha">
                            @error('g-recaptcha-response')
                                <p class="text-red-600 text-sm mt-2 text-center" data-server-error="true" data-field="captcha">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                @endif

                <!-- Login Button (same gradient as sign-up) -->
                <button type="submit" class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-5 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200">
                    Yes, log me in
                </button>
            </form>

            <!-- Footer links -->
            <div class="text-center border-t border-gray-200 mt-8 pt-6 space-y-2">
                <p class="text-gray-700 text-sm mb-2">
                    Forgot your login details?
                    <a href="{{ url('/reset-password') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">you can reset it here</a>
                </p>
                <p class="text-gray-700 text-sm">
                    If you haven't signed up before,
                    <a href="{{ url('/signup') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">you can sign up here</a>
                </p>
                <p class="text-gray-500 text-sm">
                    By signing in, you agree to our
                    <a href="{{ route('terms-and-conditions') }}" class="text-[#e04ecb] underline">Terms</a>
                    and
                    <a href="{{ route('privacy-policy') }}" class="text-[#e04ecb] underline">Privacy Policy</a>.
                </p>
                <p class="text-gray-700 text-xs mt-3 leading-relaxed">
                    Legal:
                    <a href="{{ route('terms-and-conditions') }}" class="text-[#e04ecb] underline">Terms & Conditions</a>,
                    <a href="{{ route('privacy-policy') }}" class="text-[#e04ecb] underline">Privacy Policy</a>,
                    <a href="{{ route('age-and-consent-policy') }}" class="text-[#e04ecb] underline">Age & Consent Policy</a>,
                    <a href="{{ route('content-moderation-policy') }}" class="text-[#e04ecb] underline">Content Moderation Policy</a>,
                    <a href="{{ route('contact-us') }}" class="text-[#e04ecb] underline">Contact/Support</a>.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('auth/js/signin.js') }}"></script>
@endpush
@endsection
