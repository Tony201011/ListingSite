@extends('layouts.frontend')

@section('content')
<div class="bg-[#f8fafc] min-h-screen py-10 text-gray-900">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Reset your HOTESCORTS password</h2>
        </div>

        <div class="mb-6 rounded-xl border border-pink-100 bg-pink-50 px-4 py-3 text-sm font-semibold text-gray-800">
            {{ $footerText?->adults_only_text ?? 'This website is intended for adults only.' }}
        </div>

        <div class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-md border border-gray-100">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 bg-[#e04ecb] rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800">Reset your password</h3>
            </div>

            <p class="text-gray-600 mb-3 text-lg">Enter your email address and we'll send you a link to reset your password.</p>
            <p class="text-xs leading-relaxed text-gray-700">
                Read:
                <a href="{{ route('age-and-consent-policy') }}" class="text-[#e04ecb] underline">Age & Consent Policy</a>,
                <a href="{{ route('content-moderation-policy') }}" class="text-[#e04ecb] underline">Content Moderation Policy</a>,
                <a href="{{ route('privacy-policy') }}" class="text-[#e04ecb] underline">Privacy Policy</a>,
                <a href="{{ route('terms-and-conditions') }}" class="text-[#e04ecb] underline">Terms & Conditions</a>.
            </p>
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

            <form method="POST" action="{{ route('reset-password.submit') }}">
                @csrf

                <!-- Email -->
                <div class="mb-6" data-field-group>
                    <label for="reset_email" class="block font-semibold text-gray-800 mb-1">Email address <span class="text-red-600">*</span></label>
                    <input type="email" id="reset_email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter your email"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base"
                           required>
                    <div data-error-container="email">
                        @error('email')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-5 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200">
                    Send Reset Link
                </button>
            </form>

            <div class="text-center border-t border-gray-200 mt-8 pt-6 space-y-2">
                <p class="text-gray-700 text-sm mb-2">
                    Remember your password?
                    <a href="{{ url('/signin') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">login here</a>
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
@endsection
