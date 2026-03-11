@extends('layouts.frontend')

@section('content')
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 pl-4">Create your free profile</h2>
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

            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <span class="text-[#e04ecb] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">No credit card required – zero obligations</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#e04ecb] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">First-page rankings in major cities (Sydney, Melbourne, Brisbane, Adelaide, Canberra, Gold Coast)</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#e04ecb] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">Unlimited photos and videos, Available NOW, Twitter promotions, touring pages, profile booster features and much more…</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#e04ecb] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">Advertise from $0.79 a day !!!</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#e04ecb] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">No charge when your profile is set to hidden.</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#e04ecb] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">Don't lose your profile – when not advertising you still have access to your profile.</span>
                </div>
            </div>
        </div>

        <form
            x-data="signupForm()"
            @submit="submitForm"
            class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100"
            method="POST"
            action="{{ route('signup.submit') }}"
        >
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Email address <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        x-model="email"
                        @blur="touched.email = true"
                        @input="touched.email = true; validate()"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                    >
                    @error('email')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                    <template x-if="touched.email && errors.email">
                        <div class="text-xs text-red-600 mt-1" x-text="errors.email"></div>
                    </template>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Nickname <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        name="nickname"
                        x-model="nickname"
                        @blur="touched.nickname = true"
                        @input="touched.nickname = true; validate()"
                        value="{{ old('nickname') }}"
                        placeholder="e.g. SexyBabe"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                    >
                    @error('nickname')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                    <template x-if="touched.nickname && errors.nickname">
                        <div class="text-xs text-red-600 mt-1" x-text="errors.nickname"></div>
                    </template>
                </div>

                <div class="mb-2 relative">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Password <span class="text-red-600">*</span>
                    </label>

                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                x-model="password"
                                @blur="touched.password = true"
                                @input="touched.password = true; validate()"
                                class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                            >

                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#e04ecb] font-semibold"
                            >
                                
                            </button>
                        </div>

                        <button
                            type="button"
                            @click="generatePassword()"
                            class="px-4 py-3 rounded-xl bg-[#fdf0fb] text-[#c13ab0] font-semibold border border-[#f3c4ea] hover:bg-[#fae3f6] transition"
                        >
                            Generate
                        </button>
                    </div>

                    <div class="mt-2">
                        <div class="flex items-center justify-between text-xs mb-1">
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

                    @error('password')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                    <template x-if="touched.password && errors.password">
                        <div class="text-xs text-red-600 mt-1" x-text="errors.password"></div>
                    </template>

                    <div
                        x-show="showPasswordPopup"
                        x-transition
                        @click.away="showPasswordPopup = false"
                        class="absolute z-20 mt-3 w-full bg-white border border-gray-200 rounded-2xl shadow-xl p-4"
                        style="display: none;"
                    >
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
                                ×
                            </button>
                        </div>

                        <div
                            class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-sm break-all text-gray-800 mb-4"
                            x-text="generatedPassword"
                        ></div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="generatePassword()"
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

                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Confirm password <span class="text-red-600">*</span>
                    </label>

                    <div class="relative">
                        <input
                            :type="showConfirmPassword ? 'text' : 'password'"
                            name="password_confirmation"
                            x-model="confirmPassword"
                            @blur="touched.confirmPassword = true"
                            @input="touched.confirmPassword = true; validate()"
                            class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                        >

                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#e04ecb] font-semibold"
                        >
                        </button>
                    </div>

                    @error('password_confirmation')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                    <template x-if="touched.confirmPassword && errors.confirmPassword">
                        <div class="text-xs text-red-600 mt-1" x-text="errors.confirmPassword"></div>
                    </template>
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
                        x-model="mobile"
                        @blur="touched.mobile = true"
                        @input="touched.mobile = true; validateMobile(); validate();"
                        value="{{ old('mobile') }}"
                        placeholder="Australian mobile (e.g. +61415573077)"
                        class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                    >
                </div>

                @error('mobile')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
                <template x-if="touched.mobile && errors.mobile">
                    <div class="text-xs text-red-600 mt-1" x-text="errors.mobile"></div>
                </template>

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

            <div class="mb-6">
                <label class="block font-semibold text-gray-800 mb-1">
                    Primary suburb <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    name="suburb"
                    x-model="suburb"
                    @blur="touched.suburb = true"
                    @input="touched.suburb = true; validate()"
                    value="{{ old('suburb') }}"
                    placeholder="Start typing your suburb..."
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                >
                <div class="text-xs text-gray-500 mt-1">We'll auto-complete from our list</div>
                @error('suburb')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
                <template x-if="touched.suburb && errors.suburb">
                    <div class="text-xs text-red-600 mt-1" x-text="errors.suburb"></div>
                </template>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-5 items-center mb-6">
                <div>
                    <label class="block font-semibold text-gray-800 mb-1">Referral code (optional)</label>
                    <input
                        type="text"
                        name="referral_code"
                        value="{{ old('referral_code') }}"
                        placeholder="Enter code if you have one"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition"
                    >
                </div>

                <div class="flex flex-col gap-0">
                    <div class="flex items-center gap-2.5 bg-gray-50 px-5 py-3 rounded-full">
                        <input
                            name="age_confirm"
                            type="checkbox"
                            id="age_confirm"
                            x-model="ageConfirm"
                            @change="touched.ageConfirm = true; validate()"
                            class="w-5 h-5 accent-[#e04ecb]"
                            {{ old('age_confirm') ? 'checked' : '' }}
                        >
                        <label for="age_confirm" class="font-semibold text-gray-800">I am 18+</label>
                    </div>
                    @error('age_confirm')
                        <div class="text-xs text-red-600 mt-1 pl-12">{{ $message }}</div>
                    @enderror
                    <template x-if="touched.ageConfirm && errors.ageConfirm">
                        <div class="text-xs text-red-600 mt-1 pl-12" x-text="errors.ageConfirm"></div>
                    </template>
                </div>
            </div>

            <div class="mb-8">
                <div class="flex justify-center">
                    <div class="g-recaptcha" data-sitekey="{{ $recaptchaSetting->site_key ?? '' }}"></div>
                </div>
                @error('g-recaptcha-response')
                    <div class="text-xs text-red-600 mt-3 text-center">{{ $message }}</div>
                @enderror
            </div>

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

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="//unpkg.com/alpinejs" defer></script>

<script>
    function signupForm() {
        return {
            email: @js(old('email', '')),
            nickname: @js(old('nickname', '')),
            password: '',
            confirmPassword: '',
            mobile: @js(old('mobile', '')),
            suburb: @js(old('suburb', '')),
            ageConfirm: {{ old('age_confirm') ? 'true' : 'false' }},
            showPassword: false,
            showConfirmPassword: false,
            showPasswordPopup: false,
            generatedPassword: '',
            copied: false,
            errors: {},
            touched: {
                email: false,
                nickname: false,
                password: false,
                confirmPassword: false,
                mobile: false,
                suburb: false,
                ageConfirm: false
            },

            validateEmail() {
                if (!this.email || !/^\S+@\S+\.\S+$/.test(this.email)) {
                    this.errors.email = 'Valid email is required.';
                } else {
                    delete this.errors.email;
                }
            },

            validateNickname() {
                if (!this.nickname || this.nickname.length < 3) {
                    this.errors.nickname = 'Nickname is required (min 3 chars).';
                } else {
                    delete this.errors.nickname;
                }
            },

            validatePassword() {
                if (!this.password) {
                    this.errors.password = 'Password is required.';
                    return;
                }

                if (this.password.length < 8) {
                    this.errors.password = 'Password must be at least 8 characters.';
                    return;
                }

                const hasUpper = /[A-Z]/.test(this.password);
                const hasLower = /[a-z]/.test(this.password);
                const hasNumber = /[0-9]/.test(this.password);
                const hasSymbol = /[^A-Za-z0-9]/.test(this.password);

                if (!(hasUpper && hasLower && hasNumber && hasSymbol)) {
                    this.errors.password = 'Use uppercase, lowercase, number and symbol for a stronger password.';
                } else {
                    delete this.errors.password;
                }
            },

            validateConfirmPassword() {
                if (!this.confirmPassword) {
                    this.errors.confirmPassword = 'Please confirm your password.';
                } else if (this.password !== this.confirmPassword) {
                    this.errors.confirmPassword = 'Passwords do not match.';
                } else {
                    delete this.errors.confirmPassword;
                }
            },

            validateMobile() {
                const ausMobile = /^\+61\d{9}$/;
                if (!this.mobile) {
                    this.errors.mobile = 'Mobile number is required.';
                } else if (!ausMobile.test(this.mobile)) {
                    this.errors.mobile = 'Only Australian mobile numbers in the format +614XXXXXXXX are allowed (e.g. +61415573077)';
                } else {
                    delete this.errors.mobile;
                }
            },

            validateSuburb() {
                if (!this.suburb) {
                    this.errors.suburb = 'Suburb is required.';
                } else {
                    delete this.errors.suburb;
                }
            },

            validateAgeConfirm() {
                if (!this.ageConfirm) {
                    this.errors.ageConfirm = 'You must confirm you are 18+';
                } else {
                    delete this.errors.ageConfirm;
                }
            },

            validate() {
                this.validateEmail();
                this.validateNickname();
                this.validatePassword();
                this.validateConfirmPassword();
                this.validateMobile();
                this.validateSuburb();
                this.validateAgeConfirm();

                return Object.keys(this.errors).length === 0;
            },

            submitForm(e) {
                Object.keys(this.touched).forEach(key => this.touched[key] = true);
                if (!this.validate()) {
                    e.preventDefault();
                }
            },

            generatePassword(length = 16) {
                const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const lower = 'abcdefghijklmnopqrstuvwxyz';
                const numbers = '0123456789';
                const symbols = '!@#$%^&*()-_=+[]{}?';
                const all = upper + lower + numbers + symbols;

                let password = '';
                password += upper[Math.floor(Math.random() * upper.length)];
                password += lower[Math.floor(Math.random() * lower.length)];
                password += numbers[Math.floor(Math.random() * numbers.length)];
                password += symbols[Math.floor(Math.random() * symbols.length)];

                for (let i = password.length; i < length; i++) {
                    password += all[Math.floor(Math.random() * all.length)];
                }

                this.generatedPassword = password
                    .split('')
                    .sort(() => Math.random() - 0.5)
                    .join('');

                this.copied = false;
                this.showPasswordPopup = true;
            },

            useGeneratedPassword() {
                this.password = this.generatedPassword;
                this.confirmPassword = this.generatedPassword;
                this.touched.password = true;
                this.touched.confirmPassword = true;
                this.validatePassword();
                this.validateConfirmPassword();
                this.showPasswordPopup = false;
            },

            async copyGeneratedPassword() {
                try {
                    await navigator.clipboard.writeText(this.generatedPassword);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1500);
                } catch (e) {
                    this.copied = false;
                }
            },

            get passwordStrength() {
                let score = 0;

                if (this.password.length >= 8) score++;
                if (/[A-Z]/.test(this.password)) score++;
                if (/[a-z]/.test(this.password)) score++;
                if (/[0-9]/.test(this.password)) score++;
                if (/[^A-Za-z0-9]/.test(this.password)) score++;

                if (!this.password) {
                    return { text: '', color: '', width: '0%' };
                }

                if (score <= 2) {
                    return { text: 'Weak', color: 'bg-red-500', width: '33%' };
                }

                if (score <= 4) {
                    return { text: 'Medium', color: 'bg-yellow-500', width: '66%' };
                }

                return { text: 'Strong', color: 'bg-green-500', width: '100%' };
            }
        }
    }
</script>
@endsection