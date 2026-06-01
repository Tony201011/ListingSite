@extends('layouts.frontend')

@push('styles')
    <style>
        .signup-invalid-focus {
            outline: 2px solid rgba(220, 38, 38, 0.35);
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.2) !important;
        }
    </style>
@endpush

@section('content')
@include('auth.partials.recaptcha-responsive-assets')

<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Create your free profile</h2>
        </div>

        <div class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-md border border-gray-100">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 bg-[#e04ecb] rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800">21 days free advertising</h3>
            </div>

            <p class="text-gray-600 mb-5 text-lg">Register today and enjoy these exclusive benefits:</p>

            <ul class="space-y-3 list-disc list-inside marker:text-[#e04ecb] marker:text-lg">
                <li class="text-gray-700">No credit card required – zero obligations</li>
                <li class="text-gray-700">First-page rankings in major cities (Sydney, Melbourne, Brisbane, Adelaide, Canberra, Gold Coast)</li>
                <li class="text-gray-700">Unlimited photos and videos, Available NOW, Twitter promotions, touring pages, profile booster features and much more…</li>
                <li class="text-gray-700">Advertise from $0.79 a day !!!</li>
                <li class="text-gray-700">No charge when your profile is set to hidden.</li>
                <li class="text-gray-700">Don't lose your profile – when not advertising you still have access to your profile.</li>
            </ul>
        </div>

        <form
            x-data="signupForm({
                email: @js(old('email', '')),
                nickname: @js(old('nickname', '')),
                mobile: @js(old('mobile', '')),
                ageConfirm: @js((bool) old('age_confirm'))
            })"
            @submit="submitForm"
            class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100"
            method="POST"
            action="{{ route('signup.submit') }}"
            autocomplete="off"
        >
            @csrf
            <input type="text" name="fake_username" autocomplete="username" tabindex="-1" class="hidden" aria-hidden="true">
            <input type="password" name="fake_password" autocomplete="new-password" tabindex="-1" class="hidden" aria-hidden="true">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="min-w-0">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Email address <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        x-ref="email"
                        x-model="email"
                        @blur="touched.email = true"
                        @input="touched.email = true; validateEmail()"
                        :aria-invalid="errors.email ? 'true' : 'false'"
                        autocomplete="off"
                        autocapitalize="none"
                        autocorrect="off"
                        spellcheck="false"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                    >
                    <div class="mt-1 min-h-5" data-error-container="email">
                        @error('email')
                            <div class="text-xs text-red-600" data-server-error="true" data-field="email">{{ $message }}</div>
                        @enderror
                        <template x-if="touched.email && errors.email">
                            <div class="text-xs text-red-600" x-text="errors.email"></div>
                        </template>
                    </div>
                </div>

                <div class="min-w-0">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Nickname <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        name="nickname"
                        x-ref="nickname"
                        x-model="nickname"
                        @blur="touched.nickname = true"
                        @input="touched.nickname = true; validateNickname()"
                        :aria-invalid="errors.nickname ? 'true' : 'false'"
                        placeholder="e.g. SexyBabe"
                        autocomplete="off"
                        autocapitalize="none"
                        autocorrect="off"
                        spellcheck="false"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                    >
                    <div class="mt-1 min-h-5" data-error-container="nickname">
                        @error('nickname')
                            <div class="text-xs text-red-600" data-server-error="true" data-field="nickname">{{ $message }}</div>
                        @enderror
                        <template x-if="touched.nickname && errors.nickname">
                            <div class="text-xs text-red-600" x-text="errors.nickname"></div>
                        </template>
                    </div>
                </div>

                <div class="min-w-0 relative">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Password <span class="text-red-600">*</span>
                    </label>

                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                x-ref="password"
                                x-model="password"
                                @blur="touched.password = true"
                                @input="touched.password = true; validatePassword(); validateConfirmPassword()"
                                :aria-invalid="errors.password ? 'true' : 'false'"
                                autocomplete="new-password"
                                class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                            >

                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#e04ecb]"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                :title="showPassword ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.252-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-4.132 5.411M15 12a3 3 0 00-4.243-2.829M9.88 9.88A3 3 0 0014.12 14.12" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="flex flex-wrap items-center justify-between text-xs mb-1 gap-y-0.5">
                            <span class="text-gray-500">Use 8+ characters with uppercase, lowercase, number and symbol</span>
                            <span
                                class="font-semibold"
                                :class="passwordStrength.text === 'Strong' ? 'text-green-600' : (passwordStrength.text === 'Medium' ? 'text-yellow-600' : 'text-red-600')"
                                x-text="passwordStrength.text"
                            ></span>
                        </div>

                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                :class="passwordStrength.color"
                                :style="`width: ${passwordStrength.width}`"
                            ></div>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 mt-2">
                        Tap Generate for a strong password suggestion
                    </div>

                    <button
                        type="button"
                        @click="generatePasswordPopup()"
                        class="mt-2 px-4 py-2 rounded-xl bg-[#fdf0fb] text-[#c13ab0] font-semibold border border-[#f3c4ea] hover:bg-[#fae3f6] transition"
                    >
                        Generate
                    </button>

                    <div class="mt-1 min-h-5" data-error-container="password">
                        @error('password')
                            <div class="text-xs text-red-600" data-server-error="true" data-field="password">{{ $message }}</div>
                        @enderror
                        <template x-if="touched.password && errors.password">
                            <div class="text-xs text-red-600" x-text="errors.password"></div>
                        </template>
                    </div>

                    <div
                        x-show="showPasswordPopup"
                        x-cloak
                        x-transition
                        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
                        @click.away="showPasswordPopup = false"
                    >
                        <div class="w-full max-w-sm bg-white border border-gray-200 rounded-2xl shadow-xl p-4">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h4 class="font-bold text-gray-800">Strong password suggestion</h4>
                                    <p class="text-sm text-gray-500">Save this password somewhere safe before using it.</p>
                                </div>
                                <button
                                    type="button"
                                    @click="showPasswordPopup = false"
                                    class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                                >
                                    &times;
                                </button>
                            </div>

                            <div
                                class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-sm break-all text-gray-800 mb-4"
                                x-text="generatedPassword"
                            ></div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="generatePasswordPopup()"
                                    class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50"
                                >
                                    Regenerate
                                </button>

                                <button
                                    type="button"
                                    @click="copyGeneratedPassword()"
                                    class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50"
                                >
                                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                </button>

                                <button
                                    type="button"
                                    @click="useGeneratedPassword()"
                                    class="px-4 py-2 rounded-lg bg-[#e04ecb] text-white font-semibold hover:bg-[#c13ab0]"
                                >
                                    Use this password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="min-w-0">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Confirm password <span class="text-red-600">*</span>
                    </label>

                    <div class="relative">
                        <input
                            :type="showConfirmPassword ? 'text' : 'password'"
                            name="password_confirmation"
                            x-ref="confirmPassword"
                            x-model="confirmPassword"
                            @blur="touched.confirmPassword = true"
                            @input="touched.confirmPassword = true; validateConfirmPassword()"
                            :aria-invalid="errors.confirmPassword ? 'true' : 'false'"
                            autocomplete="new-password"
                            class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                        >

                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#e04ecb]"
                            :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                            :title="showConfirmPassword ? 'Hide password' : 'Show password'"
                        >
                            <svg x-show="!showConfirmPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                            </svg>
                            <svg x-show="showConfirmPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.252-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-4.132 5.411M15 12a3 3 0 00-4.243-2.829M9.88 9.88A3 3 0 0014.12 14.12" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-1 min-h-5" data-error-container="confirmPassword">
                        @error('password_confirmation')
                            <div class="text-xs text-red-600" data-server-error="true" data-field="confirmPassword">{{ $message }}</div>
                        @enderror
                        <template x-if="touched.confirmPassword && errors.confirmPassword">
                            <div class="text-xs text-red-600" x-text="errors.confirmPassword"></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="my-6">
                <label class="block font-semibold text-gray-800 mb-1">
                    Mobile number <span class="text-red-600">*</span>
                </label>

                <div class="flex gap-2.5">
                    <select class="w-24 px-3 py-3 border-2 border-gray-200 rounded-xl bg-white text-gray-800 font-semibold opacity-100 disabled:bg-gray-100 disabled:text-gray-800 disabled:font-semibold focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20" disabled>
                        <option value="+61" selected>🇦🇺</option>
                    </select>

                    <input
                        name="mobile"
                        type="tel"
                        x-ref="mobile"
                        x-model="mobile"
                        @blur="touched.mobile = true"
                        @input="touched.mobile = true; validateMobile()"
                        :aria-invalid="errors.mobile ? 'true' : 'false'"
                        placeholder="Australian mobile (e.g. 04XXXXXXXX)"
                        autocomplete="off"
                        class="flex-1 min-w-0 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                    >
                </div>

                <div class="mt-1 min-h-5" data-error-container="mobile">
                    @error('mobile')
                        <div class="text-xs text-red-600" data-server-error="true" data-field="mobile">{{ $message }}</div>
                    @enderror
                    <template x-if="touched.mobile && errors.mobile">
                        <div class="text-xs text-red-600" x-text="errors.mobile"></div>
                    </template>
                </div>

                <div class="bg-pink-50 rounded-2xl p-5 mt-4 flex gap-4 items-start">
                    <div class="w-8 h-8 bg-[#e04ecb] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8 10a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.574 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-[#c13ab0] text-base md:text-lg">We verify every babe.</span>
                        <span class="text-gray-800 text-base md:text-lg font-medium block mt-1">
                            One of our moderators will call to confirm. We <span class="font-semibold text-[#c13ab0]">never</span> publish or share your number without permission.
                        </span>
                        <div class="mt-2 text-[#c13ab0] text-sm">📱 Australian mobile only</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-5 items-start mb-6">
                <div>
                    <label class="block font-semibold text-gray-800 mb-1">Referral code (optional)</label>
                    <input
                        type="text"
                        name="referral_code"
                        value="{{ old('referral_code', request('ref')) }}"
                        placeholder="Enter code if you have one"
                        autocomplete="off"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition"
                    >
                </div>

                <div class="flex flex-col gap-0">
                    <div class="flex items-center gap-2.5 bg-gray-50 px-5 py-3 rounded-full">
                        <input
                            name="age_confirm"
                            type="checkbox"
                            id="age_confirm"
                            x-ref="ageConfirm"
                            x-model="ageConfirm"
                            @change="touched.ageConfirm = true; validateAgeConfirm()"
                            :aria-invalid="errors.ageConfirm ? 'true' : 'false'"
                            class="w-5 h-5 accent-[#e04ecb]"
                            {{ old('age_confirm') ? 'checked' : '' }}
                        >
                        <label for="age_confirm" class="font-semibold text-gray-800">I am 18+</label>
                    </div>
                    <div class="mt-1 min-h-5" data-error-container="ageConfirm">
                        @error('age_confirm')
                            <div class="text-xs text-red-600 pl-12" data-server-error="true" data-field="ageConfirm">{{ $message }}</div>
                        @enderror
                        <template x-if="touched.ageConfirm && errors.ageConfirm">
                            <div class="text-xs text-red-600 pl-12" x-text="errors.ageConfirm"></div>
                        </template>
                    </div>
                </div>
            </div>

            @if ($shouldUseRecaptcha ?? false)
                <div class="mb-8">
                    <div class="flex justify-center">
                        <div data-recaptcha-container x-ref="captcha" tabindex="-1" class="w-full max-w-full overflow-hidden">
                            <div class="g-recaptcha" data-sitekey="{{ $recaptchaSetting->site_key ?? '' }}"></div>
                        </div>
                    </div>
                    @error('g-recaptcha-response')
                        <div class="text-xs text-red-600 mt-3 text-center" data-server-error="true" data-field="captcha" data-error-container="captcha">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-5 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200"
            >
                Yes, sign me up — it's free
            </button>

            <p class="text-center mt-5 text-gray-500 text-sm">
                By signing up, you agree to our
                <a href="{{ route('terms-and-conditions') }}" class="text-[#e04ecb] underline">Terms</a>
                and
                <a href="{{ route('privacy-policy') }}" class="text-[#e04ecb] underline">Privacy Policy</a>.
            </p>
        </form>
    </div>
</div>

@if ($shouldUseRecaptcha ?? false)
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif

@push('scripts')
    <script src="{{ asset('auth/js/password-tools.js') }}"></script>
    <script src="{{ asset('auth/js/signup.js') }}"></script>
@endpush
@endsection
