@extends('layouts.frontend')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <a href="javascript:history.back()"
            class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] mb-4 text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Go back
        </a>

        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#e04ecb] pl-4">
                Verify Your <span class="text-[#e04ecb]">Number</span>
            </h2>
        </div>

        <p class="text-gray-600 mb-6">
            We've sent a verification code to your phone. Please enter it below.
        </p>

        <hr class="border-t-2 border-gray-200 mb-8">

        <div class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">

            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-8 flex justify-between items-center">
                <div>
                    <div class="text-sm text-gray-500">Verifying number</div>
                    <div class="text-xl font-semibold text-gray-800">
                        {{ $userData->mobile ?? '' }}
                    </div>
                </div>

                <a href="{{ url('/signup') }}" class="text-[#e04ecb] font-medium text-sm">
                    Change
                </a>
            </div>

            <div
                x-data="otpVerification({
                    verifyUrl: @js(route('verify.otp')),
                    resendUrl: @js(route('resend.otp')),
                    timer: @js(max((int) ($remainingTime ?? 60), 0))
                })"
            >
                <form @submit.prevent="verifyOTP">

                    <!-- OTP INPUTS -->
                    <div class="flex justify-center gap-2 mb-6">
                        <template x-for="(digit, index) in otpDigits" :key="index">
                            <input
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                :value="otpDigits[index]"
                                @input="handleInput(index, $event)"
                                @keydown.backspace="handleBackspace(index, $event)"
                                @keydown.arrow-left.prevent="focusInput(index - 1)"
                                @keydown.arrow-right.prevent="focusInput(index + 1)"
                                @paste="handlePaste($event)"
                                class="otp-input w-14 h-14 text-center text-2xl font-extrabold border-2 border-gray-400 rounded-xl focus:border-[#e04ecb]"
                            >
                        </template>
                    </div>

                    <!-- TIMER -->
                    <div class="flex justify-between text-sm mb-6">
                        <span x-text="timerText" class="font-bold bg-yellow-100 px-3 py-1 rounded"></span>

                        <button
                            type="button"
                            @click="resendOTP"
                            :disabled="!canResend"
                            class="text-[#e04ecb] font-semibold disabled:opacity-50"
                        >
                            <span x-text="resendText"></span>
                        </button>
                    </div>

                    <!-- STATUS -->
                    <div
                        x-show="verificationStatus"
                        x-transition
                        class="p-4 rounded-xl mb-6"
                        :class="verificationStatus?.type === 'success'
                            ? 'bg-green-50 text-green-700'
                            : 'bg-red-50 text-red-700'"
                    >
                        <span x-text="verificationStatus?.message"></span>
                    </div>

                    <button
                        type="submit"
                        :disabled="!isOtpComplete || isVerifying"
                        class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold py-4 rounded-full disabled:opacity-50"
                    >
                        <span x-text="isVerifying ? 'Verifying...' : 'VERIFY & CONTINUE'"></span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('auth/js/otp-verification.js') }}"></script>
@endpush

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
