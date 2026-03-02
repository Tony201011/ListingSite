@extends('layouts.frontend')

@section('content')
<!-- ================= HERO BANNER (same as sign‑in) ================= -->
<div class="relative overflow-hidden bg-gradient-to-r from-[#e04ecb] to-[#c13ab0]">
    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop');"></div>
    <div class="relative z-10 max-w-6xl mx-auto px-5 py-16 text-center">
        <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-2 drop-shadow-lg">hotescorts.com.au</h1>
        <p class="text-xl text-white/90 tracking-widest">REAL WOMEN NEAR YOU</p>
    </div>
</div>

<!-- Main Content -->
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <!-- Optional back link (same style as sign‑in) -->
        <a href="javascript:history.back()" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Go back
        </a>

        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#e04ecb] pl-4">
                Reset your password
            </h2>
        </div>

        <!-- Description -->
        <p class="text-gray-600 mb-8 text-lg">
            To reset your password, please provide your email address.
        </p>

        <!-- Reset Password Form Card -->
        <div class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">
            <form method="POST" action="#">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label class="block font-semibold text-gray-800 mb-1">Email address <span class="text-red-600">*</span></label>
                    <input type="email"
                           placeholder="Enter your email address"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-gray-50 focus:bg-white"
                           required>
                </div>

                <!-- reCAPTCHA (same as sign‑in) -->
                <div class="mb-8">
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-7 h-7 bg-[#e04ecb] rounded-lg flex items-center justify-center text-white font-bold">✓</div>
                        <span class="text-gray-800">I'm not a robot</span>
                        <div class="ml-auto flex items-center gap-2">
                            <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="reCAPTCHA" class="w-6 h-6 opacity-70">
                            <span class="text-xs text-gray-400">reCAPTCHA</span>
                        </div>
                    </div>
                </div>

                <!-- Reset Password Button -->
                <button type="submit"
                        class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-4 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200">
                    Reset password
                </button>
            </form>

            <!-- Link to login -->
            <div class="text-center border-t border-gray-200 mt-8 pt-6">
                <p class="text-gray-500 text-sm">
                    <a href="#" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">
                        Login Here
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- No extra CSS needed – all styles are handled by Tailwind utilities -->
@endsection
