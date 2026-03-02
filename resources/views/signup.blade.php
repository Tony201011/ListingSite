@extends('layouts.frontend')

@section('content')
<!-- ================= HERO BANNER ================= -->
<div class="relative overflow-hidden bg-gradient-to-r from-[#667eea] to-[#764ba2]">
    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop');"></div>
    <div class="relative z-10 max-w-6xl mx-auto px-5 py-16 text-center">
        <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-2 drop-shadow-lg">hotescorts.com.au</h1>
        <p class="text-xl text-white/90 tracking-widest">REAL WOMEN NEAR YOU</p>
    </div>
</div>

<!-- Main Content -->
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <!-- Sign up Header with Steps -->
        <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#9f7aea] pl-4">Create your free profile</h2>
            <div class="flex items-center gap-4 bg-white px-5 py-2 rounded-full shadow-sm">
                <span class="text-[#9f7aea] font-semibold">Step 1 of 3</span>
                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="w-1/3 h-full bg-[#9f7aea]"></div>
                </div>
            </div>
        </div>

        <!-- Free Trial & Benefits Card (Fixed with purple bullets and full text) -->
        <div class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-md border border-gray-100">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 bg-[#9f7aea] rounded-xl flex items-center justify-center">
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
                    <span class="text-[#9f7aea] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">No credit card required – zero obligations</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#9f7aea] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">First‑page rankings in major cities (Sydney, Melbourne, Brisbane, Adelaide, Canberra, Gold Coast)</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#9f7aea] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">Unlimited photos and videos, Available NOW, Twitter promotions, touring pages, profile booster features and much more…</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#9f7aea] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">Advertise from $0.79 a day !!!</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#9f7aea] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">No charge when your profile is set to hidden.</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-[#9f7aea] font-bold text-xl leading-5">•</span>
                    <span class="text-gray-700">Don't lose your profile – when not advertising you still have access to your profile.</span>
                </div>
            </div>
        </div>

        <!-- Registration Form -->
        <form class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100" method="POST" action="#">
            @csrf

            <!-- Two-column layout for some fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Email -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Email address <span class="text-red-600">*</span></label>
                    <input type="email" value="s8811w@gmail.com" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20 transition" required>
                </div>
                <!-- Nickname -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Nickname <span class="text-red-600">*</span></label>
                    <input type="text" placeholder="e.g. SexyBabe" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20 transition" required>
                </div>
                <!-- Password -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Password <span class="text-red-600">*</span></label>
                    <input type="password" value="**********" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20 transition" required>
                    <div class="text-xs text-gray-500 mt-1">8‑20 characters, letters & numbers recommended</div>
                </div>
                <!-- Confirm Password -->
                <div class="mb-2">
                    <label class="block font-semibold text-gray-800 mb-1">Confirm password <span class="text-red-600">*</span></label>
                    <input type="password" value="**********" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20 transition" required>
                </div>
            </div>

            <!-- Mobile Number with styled verification -->
            <div class="my-6">
                <label class="block font-semibold text-gray-800 mb-1">Mobile number <span class="text-red-600">*</span></label>
                <div class="flex gap-2.5">
                    <select class="w-24 px-3 py-3 border-2 border-gray-200 rounded-xl bg-white focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20">
                        <option value="+61">🇦🇺 +61</option>
                        <option value="+64">🇳🇿 +64</option>
                        <option value="+44">🇬🇧 +44</option>
                    </select>
                    <input type="tel" placeholder="Australian mobile" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20 transition">
                </div>
                <!-- Verification callout -->
                <div class="bg-purple-50 rounded-2xl p-5 mt-4 flex gap-4 items-start">
                    <div class="w-8 h-8 bg-[#9f7aea] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8 10a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.574 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-purple-800">We verify every babe.</span> One of our moderators will call to confirm. We <span class="font-semibold">never</span> publish or share your number without permission.
                        <div class="mt-2 text-purple-700 text-sm">📱 Australian mobile only</div>
                    </div>
                </div>
            </div>

            <!-- Suburb with autocomplete hint -->
            <div class="mb-6">
                <label class="block font-semibold text-gray-800 mb-1">Primary suburb <span class="text-red-600">*</span></label>
                <input type="text" placeholder="Start typing your suburb..." class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20 transition" required>
                <div class="text-xs text-gray-500 mt-1">We'll auto‑complete from our list</div>
            </div>

            <!-- Referral code & Age confirmation in one row -->
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-5 items-center mb-6">
                <div>
                    <label class="block font-semibold text-gray-800 mb-1">Referral code (optional)</label>
                    <input type="text" placeholder="Enter code if you have one" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#9f7aea] focus:ring-2 focus:ring-[#9f7aea]/20 transition">
                </div>
                <div class="flex items-center gap-2.5 bg-gray-50 px-5 py-3 rounded-full">
                    <input type="checkbox" id="age_confirm" class="w-5 h-5 accent-[#9f7aea]" required>
                    <label for="age_confirm" class="font-semibold text-gray-800">I am 18+</label>
                </div>
            </div>

            <!-- reCAPTCHA styled minimal version -->
            <div class="mb-8">
                <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-7 h-7 bg-[#9f7aea] rounded-lg flex items-center justify-center text-white font-bold">✓</div>
                    <span class="text-gray-800">I'm not a robot</span>
                    <div class="ml-auto flex items-center gap-2">
                        <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="reCAPTCHA" class="w-6 h-6 opacity-70">
                        <span class="text-xs text-gray-400">reCAPTCHA</span>
                    </div>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="w-full bg-gradient-to-r from-[#9f7aea] to-[#6b46c1] text-white font-bold text-xl py-5 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200">
                Yes, sign me up — it's free
            </button>

            <p class="text-center mt-5 text-gray-500 text-sm">
                By signing up, you agree to our <a href="#" class="text-[#9f7aea] underline">Terms</a> and <a href="#" class="text-[#9f7aea] underline">Privacy Policy</a>.
            </p>
        </form>
    </div>
</div>
@endsection
