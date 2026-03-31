@extends('layouts.frontend')

@section('content')

<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <!-- Optional back link (can be removed if not needed) -->
        <a href="javascript:history.back()" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Go back
        </a>


        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#e04ecb] pl-4">Login to your HOTESCORTS profile</h2>
        </div>

        <div class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">
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

            <form method="POST" action="{{ route('signin.submit') }}">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label class="block font-semibold text-gray-800 mb-1">Email address <span class="text-red-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base" placeholder="Enter your email" required>
                    @error('email')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label class="block font-semibold text-gray-800 mb-1">Password <span class="text-red-600">*</span></label>
                    <input type="password" name="password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base" placeholder="Enter your password" required>
                    @error('password')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keep me logged in (styled like the age confirmation pill) -->
                <div class="mb-6">
                    <div class="inline-flex items-center gap-2.5 bg-gray-50 px-5 py-3 rounded-full">
                        <input type="checkbox" id="keep_logged_in" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="w-5 h-5 accent-[#e04ecb]">
                        <label for="keep_logged_in" class="font-semibold text-gray-800">Keep me logged in on this device</label>
                    </div>
                </div>

                @if ($shouldUseRecaptcha ?? false)
                    <div class="mb-8">
                        <div class="flex justify-center">
                                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSetting->site_key ?? '' }}"></div>
                        </div>
                        @error('g-recaptcha-response')
                            <p class="text-red-600 text-sm mt-2 text-center">{{ $message }}</p>
                        @enderror
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
                <p class="text-gray-500 text-sm mb-2">
                    Forgot your login details?
                    <a href="{{ url('/reset-password') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">you can reset it here</a>
                </p>
                <p class="text-gray-500 text-sm">
                    If you haven't signed up before,
                    <a href="{{ url('/signup') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">you can sign up here</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
