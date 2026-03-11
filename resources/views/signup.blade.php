@extends('layouts.frontend')

@section('content')
<!-- Main Content -->
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <!-- Sign up Header with Steps -->
        <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 pl-4">Create your free profile</h2>
        </div>

        <!-- Free Trial & Benefits Card (Fixed with purple bullets and full text) -->
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
                    <span class="text-gray-700">First‑page rankings in major cities (Sydney, Melbourne, Brisbane, Adelaide, Canberra, Gold Coast)</span>
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

        <!-- Registration Form -->
        <form
            x-data="{
                email: '',
                nickname: '',
                password: '',
                confirmPassword: '',
                mobile: '',
                suburb: '',
                ageConfirm: false,
                errors: {},
                touched: { email: false, nickname: false, password: false, confirmPassword: false, mobile: false, suburb: false, ageConfirm: false },
                validate() {
                    // Email
                    if (!this.email || !/^\S+@\S+\.\S+$/.test(this.email)) {
                        this.errors.email = 'Valid email is required.';
                    } else {
                        delete this.errors.email;
                    }
                    // Nickname
                    if (!this.nickname || this.nickname.length < 3) {
                        this.errors.nickname = 'Nickname is required (min 3 chars).';
                    } else {
                        delete this.errors.nickname;
                    }
                    // Password
                    if (!this.password || this.password.length < 8) {
                        this.errors.password = 'Password must be at least 8 characters.';
                    } else {
                        delete this.errors.password;
                    }
                    // Confirm Password
                    if (this.password !== this.confirmPassword) {
                        this.errors.confirmPassword = 'Passwords do not match.';
                    } else {
                        delete this.errors.confirmPassword;
                    }
                    // Mobile
                    if (!this.mobile || this.mobile.length < 8) {
                        this.errors.mobile = 'Valid mobile number required.';
                    } else {
                        delete this.errors.mobile;
                    }
                    // Suburb
                    if (!this.suburb) {
                        this.errors.suburb = 'Suburb is required.';
                    } else {
                        delete this.errors.suburb;
                    }
                    // Age Confirm
                    if (!this.ageConfirm) {
                        this.errors.ageConfirm = 'You must confirm you are 18+';
                    } else {
                        delete this.errors.ageConfirm;
                    }
                    return Object.keys(this.errors).length === 0;
                },
                submitForm(e) {
                    // Mark all fields as touched on submit
                    Object.keys(this.touched).forEach(k => this.touched[k] = true);
                    if (!this.validate()) {
                        e.preventDefault();
                    }
                }
            }"
            @submit="submitForm"
            class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100"
            method="POST" action="{{ route('signup.submit') }}">
            @csrf

            <!-- Two-column layout for some fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Email -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Email address <span class="text-red-600">*</span></label>
                    <input type="email" x-model="email" @blur="touched.email = true" @input="touched.email = true; validate()" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                    <template x-if="touched.email && errors.email"><div class="text-xs text-red-600 mt-1" x-text="errors.email"></div></template>
                    <template x-if="touched.email && !errors.email && email === ''"><div class="text-xs text-red-600 mt-1">This field is required</div></template>
                </div>
                <!-- Nickname -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Nickname <span class="text-red-600">*</span></label>
                    <input type="text" x-model="nickname" @blur="touched.nickname = true" @input="touched.nickname = true; validate()" placeholder="e.g. SexyBabe" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                    <template x-if="touched.nickname && errors.nickname"><div class="text-xs text-red-600 mt-1" x-text="errors.nickname"></div></template>
                    <template x-if="touched.nickname && !errors.nickname && nickname === ''"><div class="text-xs text-red-600 mt-1">This field is required</div></template>
                </div>
                <!-- Password -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Password <span class="text-red-600">*</span></label>
                    <input type="password" x-model="password" @blur="touched.password = true" @input="touched.password = true; validate()" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                    <div class="text-xs text-gray-500 mt-1">8‑20 characters, letters & numbers recommended</div>
                    <template x-if="touched.password && errors.password"><div class="text-xs text-red-600 mt-1" x-text="errors.password"></div></template>
                    <template x-if="touched.password && !errors.password && password === ''"><div class="text-xs text-red-600 mt-1">This field is required</div></template>
                </div>
                <!-- Confirm Password -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Confirm password <span class="text-red-600">*</span></label>
                    <input type="password" x-model="confirmPassword" @blur="touched.confirmPassword = true" @input="touched.confirmPassword = true; validate()" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                    <template x-if="touched.confirmPassword && errors.confirmPassword"><div class="text-xs text-red-600 mt-1" x-text="errors.confirmPassword"></div></template>
                    <template x-if="touched.confirmPassword && !errors.confirmPassword && confirmPassword === ''"><div class="text-xs text-red-600 mt-1">This field is required</div></template>
                </div>
            </div>

            <!-- Mobile Number with styled verification -->
            <div class="my-6">
                <label class="block font-semibold text-gray-800 mb-1">Mobile number <span class="text-red-600">*</span></label>
                <div class="flex gap-2.5">
                    <select class="w-24 px-3 py-3 border-2 border-gray-200 rounded-xl bg-white text-gray-800 font-semibold opacity-100 disabled:bg-gray-100 disabled:text-gray-800 disabled:font-semibold focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20" disabled>
                        <option value="+61" selected>🇦🇺 +61</option>
                    </select>
                    <input
                        type="tel"
                        x-model="mobile"
                        @blur="touched.mobile = true"
                        @input="touched.mobile = true; validate();
                            // Accepts 04xxxxxxxx or 614xxxxxxxx, optional spaces
                            const ausMobile = /^(04\d{8}|614\d{8})$/;
                            let cleaned = mobile.replace(/\D/g, '');
                            if (cleaned.startsWith('61')) {
                                cleaned = '0' + cleaned.slice(2);
                            }
                            if (!ausMobile.test(cleaned)) {
                                errors.mobile = 'Enter a valid Australian mobile (e.g. 0412345678 or 61412345678)';
                            } else {
                                delete errors.mobile;
                            }
                        "
                        placeholder="Australian mobile (e.g. 0412 345 678)"
                        class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                    >
                </div>
                <template x-if="touched.mobile && errors.mobile"><div class="text-xs text-red-600 mt-1" x-text="errors.mobile"></div></template>
                <template x-if="touched.mobile && !errors.mobile && mobile === ''"><div class="text-xs text-red-600 mt-1">This field is required</div></template>
                <!-- Verification callout -->
                <div class="bg-pink-50 rounded-2xl p-5 mt-4 flex gap-4 items-start">
                    <div class="w-8 h-8 bg-[#e04ecb] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8 10a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.574 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-[#c13ab0] text-base md:text-lg">We verify every babe.</span>
                        <span class="text-gray-800 text-base md:text-lg font-medium block mt-1">One of our moderators will call to confirm. We <span class="font-semibold text-[#c13ab0]">never</span> publish or share your number without permission.</span>
                        <div class="mt-2 text-[#c13ab0] text-sm">📱 Australian mobile only</div>
                    </div>
                </div>
            </div>

            <!-- Suburb with autocomplete hint -->
            <div class="mb-6">
                <label class="block font-semibold text-gray-800 mb-1">Primary suburb <span class="text-red-600">*</span></label>
                <input type="text" x-model="suburb" @blur="touched.suburb = true" @input="touched.suburb = true; validate()" placeholder="Start typing your suburb..." class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                <div class="text-xs text-gray-500 mt-1">We'll auto‑complete from our list</div>
                <template x-if="touched.suburb && errors.suburb"><div class="text-xs text-red-600 mt-1" x-text="errors.suburb"></div></template>
                <template x-if="touched.suburb && !errors.suburb && suburb === ''"><div class="text-xs text-red-600 mt-1">This field is required</div></template>
            </div>

            <!-- Referral code & Age confirmation in one row -->
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-5 items-center mb-6">
                <div>
                    <label class="block font-semibold text-gray-800 mb-1">Referral code (optional)</label>
                    <input type="text" placeholder="Enter code if you have one" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition">
                </div>
                                        <div class="flex flex-col gap-0">
                                            <div class="flex items-center gap-2.5 bg-gray-50 px-5 py-3 rounded-full">
                                                <input type="checkbox" id="age_confirm" x-model="ageConfirm" @change="touched.ageConfirm = true; validate()" class="w-5 h-5 accent-[#e04ecb]">
                                                <label for="age_confirm" class="font-semibold text-gray-800">I am 18+</label>
                                            </div>
                                            <template x-if="touched.ageConfirm && errors.ageConfirm">
                                                <div class="text-xs text-red-600 mt-1 pl-12" x-text="errors.ageConfirm"></div>
                                            </template>
                                        </div>
            </div>

            <!-- Google reCAPTCHA widget -->
<div class="flex justify-center mb-8">

    <div class="flex items-center justify-between w-full max-w-md bg-gray-100 border border-gray-200 rounded-xl px-5 py-4">

        <!-- Left side -->
        <div class="flex items-center gap-3">

            <!-- Custom check icon -->
            <div class="w-7 h-7 bg-[#e04ecb] rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <span class="text-gray-700 font-medium text-sm">
                I'm not a robot
            </span>

        </div>

        <!-- Google recaptcha -->
        <div>
            <div class="g-recaptcha scale-90 origin-right"
                 data-sitekey="{{ $recaptcha->site_key ?? '' }}">
            </div>
        </div>

    </div>

</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

            <!-- Submit button -->
            <button type="submit" class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-5 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200">
                Yes, sign me up — it's free
            </button>

            <p class="text-center mt-5 text-gray-500 text-sm">
                By signing up, you agree to our <a href="{{ route('terms-and-conditions') }}" class="text-[#e04ecb] underline">Terms</a> and <a href="{{ route('privacy-policy') }}" class="text-[#e04ecb] underline">Privacy Policy</a>.
            </p>
        </form>
    </div>
</div>
@endsection
