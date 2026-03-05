@extends('layouts.frontend')

@section('content')
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

        <!-- Header with purple left border (matches sign‑in) -->
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#e04ecb] pl-4">
                Verify Your <span class="text-[#e04ecb]">Number</span>
            </h2>
        </div>

        <!-- Description -->
        <p class="text-gray-600 mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#e04ecb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            We've sent a verification code to your phone. Please enter it below.
        </p>

        <hr class="border-t-2 border-gray-200 mb-8">

        <!-- OTP Verification Card (styled exactly like sign‑in form) -->
        <div class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">
            <!-- Phone number display (pill style) -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#e04ecb] rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Verifying number</div>
                        <div class="text-xl font-semibold text-gray-800">+61 412 345 678</div>
                    </div>
                </div>
                <a href="{{ url('/signup') }}" class="text-[#e04ecb] font-medium hover:text-[#c13ab0] transition text-sm">
                    Change <i class="fas fa-chevron-right text-xs ml-1"></i>
                </a>
            </div>

            <!-- Alpine Component -->
            <div x-data="otpVerification()" x-init="init()">
                <form @submit.prevent="verifyOTP">
                    <!-- OTP Input Fields with smaller boxes -->
                    <div class="mb-6">
                        <label class="block font-semibold text-gray-800 mb-3">
                            <svg class="w-5 h-5 inline mr-2 text-[#e04ecb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Enter 6‑digit verification code
                        </label>

                        <!-- OTP Digits Row – tighter, centered spacing -->
                        <div class="flex items-center justify-center gap-1.5 sm:gap-2 mb-4">
                            <template x-for="(digit, index) in 6" :key="index">
                                <input type="text"
                                       x-model="otpDigits[index]"
                                       :ref="'otpInput' + index"
                                       @input="handleInput(index, $event)"
                                       @keydown="handleKeydown(index, $event)"
                                       @paste="handlePaste"
                                       @focus="focusedIndex = index"
                                       maxlength="1"
                                       class="w-12 h-12 sm:w-14 sm:h-14 text-center text-xl font-bold bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                                       :class="{'border-[#e04ecb] ring-2 ring-[#e04ecb]/20': focusedIndex === index}">
                            </template>
                        </div>

                        <!-- Timer and Resend – more compact -->
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-1.5 bg-gray-50 px-3 py-1.5 rounded-full">
                                <svg class="w-4 h-4 text-[#e04ecb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="timerText" class="text-gray-700"></span>
                            </div>
                            <button type="button"
                                    @click="resendOTP"
                                    :disabled="!canResend"
                                    class="bg-transparent border-0 text-[#e04ecb] font-semibold cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition"
                                    x-text="resendText">
                            </button>
                        </div>
                    </div>

                    <!-- Verification Status Message -->
                    <div x-show="verificationStatus"
                         x-transition
                         class="p-4 rounded-xl mb-6 flex items-center gap-3 border"
                         :class="verificationStatus?.type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'">
                        <i :class="verificationStatus?.type === 'success' ? 'fas fa-check-circle text-green-600' : 'fas fa-exclamation-circle text-red-600'"></i>
                        <span x-text="verificationStatus?.message"></span>
                    </div>

                    <!-- Info Box (matching sign‑in's style) -->
                    <div class="bg-pink-50 border-l-4 border-[#e04ecb] rounded-xl p-4 text-gray-700 text-sm my-6">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-[#e04ecb] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <span class="font-semibold">Why we verify your number?</span> We verify all our babes to ensure authenticity. This helps us maintain a safe community of real babes only. <span class="font-semibold">We will NEVER publish or share this phone number without your permission.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button (same gradient as sign‑in) -->
                    <button type="submit"
                            :disabled="!isOtpComplete || isVerifying"
                            class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-4 rounded-full shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle" x-show="!isVerifying"></i>
                        <i class="fas fa-spinner fa-spin" x-show="isVerifying" x-cloak></i>
                        <span x-text="isVerifying ? 'Verifying...' : 'VERIFY & CONTINUE'"></span>
                    </button>
                </form>

                <!-- Help & Back Links (same as sign‑in footer links) -->
                <p class="text-center text-gray-500 text-sm mt-6">
                    <i class="fas fa-question-circle text-[#e04ecb] mr-1"></i>
                    Didn't receive the code?
                    <button type="button" @click="showHelp" class="bg-transparent border-0 text-[#e04ecb] font-semibold underline cursor-pointer hover:text-[#c13ab0]">
                        Contact support
                    </button>
                </p>

                <div class="text-center mt-4">
                    <a href="{{ url('/signup') }}" class="text-gray-500 hover:text-[#e04ecb] transition text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Signup
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Note (same as sign‑in) -->
        <p class="text-center text-gray-400 text-sm mt-8">
            <i class="fas fa-heart text-[#e04ecb]"></i>
            Your privacy and security are our top priorities
            <i class="fas fa-heart text-[#e04ecb]"></i>
        </p>
    </div>
</div>

<!-- Alpine.js Component (unchanged) -->
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function otpVerification() {
        return {
            otpDigits: ['', '', '', '', '', ''],
            focusedIndex: 0,
            timer: 60,
            canResend: false,
            resendText: 'Resend Code',
            timerText: 'Code expires in 01:00',
            isVerifying: false,
            verificationStatus: null,

            init() {
                this.startTimer();
                this.$nextTick(() => {
                    const firstInput = this.$refs['otpInput0'];
                    if (firstInput && firstInput[0]) {
                        firstInput[0].focus();
                    }
                });
            },

            startTimer() {
                this.canResend = false;
                this.timer = 60;
                this.updateTimerText();

                const interval = setInterval(() => {
                    if (this.timer > 0) {
                        this.timer--;
                        this.updateTimerText();
                    } else {
                        this.canResend = true;
                        this.resendText = 'Resend Code';
                        clearInterval(interval);
                    }
                }, 1000);
            },

            updateTimerText() {
                const minutes = Math.floor(this.timer / 60);
                const seconds = this.timer % 60;
                this.timerText = `Code expires in ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            },

            handleInput(index, event) {
                const value = event.target.value;

                // Only allow numbers
                if (value && !/^\d+$/.test(value)) {
                    this.otpDigits[index] = '';
                    return;
                }

                // Auto-advance to next input
                if (value && index < 5) {
                    this.focusedIndex = index + 1;
                    this.$nextTick(() => {
                        const nextInput = this.$refs['otpInput' + (index + 1)];
                        if (nextInput && nextInput[0]) {
                            nextInput[0].focus();
                        }
                    });
                }
            },

            handleKeydown(index, event) {
                // Handle backspace
                if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                    this.focusedIndex = index - 1;
                    this.$nextTick(() => {
                        const prevInput = this.$refs['otpInput' + (index - 1)];
                        if (prevInput && prevInput[0]) {
                            prevInput[0].focus();
                        }
                    });
                }

                // Handle left arrow
                if (event.key === 'ArrowLeft' && index > 0) {
                    this.focusedIndex = index - 1;
                    this.$nextTick(() => {
                        const prevInput = this.$refs['otpInput' + (index - 1)];
                        if (prevInput && prevInput[0]) {
                            prevInput[0].focus();
                        }
                    });
                }

                // Handle right arrow
                if (event.key === 'ArrowRight' && index < 5) {
                    this.focusedIndex = index + 1;
                    this.$nextTick(() => {
                        const nextInput = this.$refs['otpInput' + (index + 1)];
                        if (nextInput && nextInput[0]) {
                            nextInput[0].focus();
                        }
                    });
                }
            },

            handlePaste(event) {
                event.preventDefault();
                const pastedData = event.clipboardData.getData('text');
                const numbers = pastedData.replace(/\D/g, '').split('');

                numbers.forEach((num, index) => {
                    if (index < 6) {
                        this.otpDigits[index] = num;
                    }
                });

                // Focus the next empty field or last field
                const nextEmptyIndex = this.otpDigits.findIndex(digit => !digit);
                if (nextEmptyIndex !== -1) {
                    this.focusedIndex = nextEmptyIndex;
                    this.$nextTick(() => {
                        const nextInput = this.$refs['otpInput' + nextEmptyIndex];
                        if (nextInput && nextInput[0]) {
                            nextInput[0].focus();
                        }
                    });
                } else {
                    this.focusedIndex = 5;
                    this.$nextTick(() => {
                        const lastInput = this.$refs.otpInput5;
                        if (lastInput && lastInput[0]) {
                            lastInput[0].focus();
                        }
                    });
                }
            },

            get isOtpComplete() {
                return this.otpDigits.every(digit => digit && digit.length === 1);
            },

            get otpCode() {
                return this.otpDigits.join('');
            },

            verifyOTP() {
                if (!this.isOtpComplete || this.isVerifying) return;

                this.isVerifying = true;
                this.verificationStatus = null;

                // Simulate API call
                setTimeout(() => {
                    if (this.otpCode === '123456') {
                        this.verificationStatus = {
                            type: 'success',
                            message: '✓ Phone number verified successfully! Redirecting...'
                        };

                        setTimeout(() => {
                            window.location.href = '/signup/success';
                        }, 2000);
                    } else {
                        this.verificationStatus = {
                            type: 'error',
                            message: '✗ Invalid verification code. Please try again.'
                        };
                        this.isVerifying = false;
                    }
                }, 1500);
            },

            resendOTP() {
                if (!this.canResend) return;

                this.resendText = 'Sending...';

                setTimeout(() => {
                    this.startTimer();
                    this.verificationStatus = {
                        type: 'success',
                        message: '✓ New code sent to your phone!'
                    };

                    setTimeout(() => {
                        this.verificationStatus = null;
                    }, 3000);
                }, 1000);
            },

            showHelp() {
                alert('Please contact support at support@hotescorts.com.au or call 1800 123 456');
            }
        }
    }
</script>

<!-- Font Awesome (if not already in layout) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Optional extra style to hide Alpine cloak -->
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
