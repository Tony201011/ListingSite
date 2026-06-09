@extends('layouts.frontend')

@push('styles')
    <style>
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

        <!-- Optional back link (can be removed if not needed) -->
        <a href="javascript:history.back()" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Go back
        </a>


        <div class="mb-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Login to your HOTESCORTS profile</h2>
        </div>

        <div class="mb-6 rounded-xl border border-pink-100 bg-pink-50 px-4 py-3 text-sm font-semibold text-gray-800">
            This website is intended for adults only.
        </div>

        <div class="mb-6 rounded-xl border border-pink-100 bg-pink-50 px-4 py-3 text-sm text-gray-800">
            <p class="font-semibold text-[#c13ab0]">We verify every babe.</p>
            <p class="mt-1">Verification and moderation checks are completed before profiles go live. Your account data and contact information are handled under our privacy and consent policies.</p>
            <p class="mt-2 text-xs leading-relaxed">
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

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                x-data="signinForm()"
                @submit="submitForm"
                method="POST"
                action="{{ route('signin.submit') }}"
                novalidate
            >
                @csrf

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
                    <label for="keep_logged_in" class="flex items-center gap-2.5 bg-gray-50 px-4 py-3 rounded-xl cursor-pointer w-full sm:w-fit">
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
                <button type="submit" class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-4 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200">
                    Login
                </button>
            </form>

            <!-- Footer links -->
            <div class="text-center border-t border-gray-200 mt-8 pt-6">
                <p class="text-gray-700 text-sm mb-2">
                    Forgot your login details?
                    <a href="{{ url('/reset-password') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">you can reset it here</a>
                </p>
                <p class="text-gray-700 text-sm">
                    If you haven't signed up before,
                    <a href="{{ url('/signup') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">you can sign up here</a>
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
