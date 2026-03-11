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

                <div x-data="otpVerification()" x-init="init()">
                    <form @submit.prevent="verifyOTP">
                        <div class="flex justify-center gap-2 mb-6">
                            <template x-for="(digit, index) in otpDigits" :key="index">
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="1"
                                    x-model="otpDigits[index]"
                                    :x-ref="'otp' + index"
                                    @input="handleInput(index, $event)"
                                    @keydown.backspace="handleBackspace(index, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:outline-none"
                                >
                            </template>
                        </div>

                        <div class="flex justify-between text-sm mb-6">
                            <span x-text="timerText"></span>

                            <button
                                type="button"
                                @click="resendOTP"
                                :disabled="!canResend"
                                class="bg-transparent border-0 text-[#e04ecb] font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-text="resendText"></span>
                            </button>
                        </div>

                        <div
                            x-show="verificationStatus"
                            x-transition
                            class="p-4 rounded-xl mb-6"
                            :class="verificationStatus && verificationStatus.type === 'success'
                                ? 'bg-green-50 text-green-700'
                                : 'bg-red-50 text-red-700'"
                        >
                            <span x-text="verificationStatus ? verificationStatus.message : ''"></span>
                        </div>

                        <button
                            type="submit"
                            :disabled="!isOtpComplete || isVerifying"
                            class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold py-4 rounded-full disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-text="isVerifying ? 'Verifying...' : 'VERIFY & CONTINUE'"></span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        window.otpTimer = {{ max((int) ($remainingTime ?? 60), 0) }};
    </script>

    <script>
        function otpVerification() {
            return {
                otpDigits: ['', '', '', '', '', ''],
                timer: window.otpTimer || 60,
                canResend: false,
                resendText: 'Resend Code',
                timerText: '',
                isVerifying: false,
                verificationStatus: null,
                intervalId: null,

                init() {
                    this.updateTimerText();

                    if (this.timer > 0) {
                        this.startTimer();
                    } else {
                        this.canResend = true;
                        this.timerText = 'Code expired';
                    }

                    this.$nextTick(() => {
                        if (this.$refs['otp0']) {
                            this.$refs['otp0'].focus();
                        }
                    });
                },

                get isOtpComplete() {
                    return this.otpDigits.every(d => d !== '');
                },

                get otpCode() {
                    return this.otpDigits.join('');
                },

                updateTimerText() {
                    if (this.timer <= 0) {
                        this.timerText = 'Code expired';
                        return;
                    }

                    let minutes = Math.floor(this.timer / 60);
                    let seconds = this.timer % 60;

                    this.timerText = `Code expires in ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                },

                startTimer() {
                    if (this.intervalId) {
                        clearInterval(this.intervalId);
                    }

                    this.canResend = false;
                    this.updateTimerText();

                    this.intervalId = setInterval(() => {
                        if (this.timer > 0) {
                            this.timer--;
                            this.updateTimerText();
                        } else {
                            clearInterval(this.intervalId);
                            this.intervalId = null;
                            this.canResend = true;
                            this.resendText = 'Resend Code';
                            this.timerText = 'Code expired';
                        }
                    }, 1000);
                },

                handleInput(index, event) {
                    let value = event.target.value.replace(/\D/g, '');

                    this.otpDigits[index] = value ? value[0] : '';

                    if (value && index < 5) {
                        this.$refs['otp' + (index + 1)].focus();
                    }
                },

                handleBackspace(index, event) {
                    if (this.otpDigits[index] === '' && index > 0) {
                        this.$refs['otp' + (index - 1)].focus();
                    }
                },

                handlePaste(event) {
                    event.preventDefault();

                    const pasted = (event.clipboardData || window.clipboardData)
                        .getData('text')
                        .replace(/\D/g, '')
                        .slice(0, 6);

                    if (!pasted) return;

                    for (let i = 0; i < 6; i++) {
                        this.otpDigits[i] = pasted[i] || '';
                    }

                    const nextIndex = Math.min(pasted.length, 5);
                    this.$nextTick(() => {
                        this.$refs['otp' + nextIndex].focus();
                    });
                },

                verifyOTP() {
                    if (!this.isOtpComplete || this.isVerifying) return;

                    this.isVerifying = true;
                    this.verificationStatus = null;

                    fetch("{{ route('verify.otp') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            otp: this.otpCode
                        })
                    })
                    .then(async (res) => {
                        const data = await res.json();
                        return { ok: res.ok, data };
                    })
                    .then(({ ok, data }) => {
                        if (ok && data.success) {
                            this.verificationStatus = {
                                type: 'success',
                                message: data.message || 'OTP verified successfully.'
                            };

                            setTimeout(() => {
                                window.location.href = data.redirect || '/dashboard';
                            }, 1200);
                        } else {
                            this.verificationStatus = {
                                type: 'error',
                                message: data.message || 'Invalid OTP.'
                            };
                            this.isVerifying = false;
                        }
                    })
                    .catch(() => {
                        this.verificationStatus = {
                            type: 'error',
                            message: 'Something went wrong while verifying OTP.'
                        };
                        this.isVerifying = false;
                    });
                },

                resendOTP() {
                    if (!this.canResend) return;

                    this.resendText = "Sending...";
                    this.verificationStatus = null;

                    fetch("{{ route('resend.otp') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(async (res) => {
                        const data = await res.json();
                        return { ok: res.ok, data };
                    })
                    .then(({ ok, data }) => {
                        if (ok && data.success) {
                            this.verificationStatus = {
                                type: 'success',
                                message: data.message || 'OTP resent successfully.'
                            };

                            this.otpDigits = ['', '', '', '', '', ''];
                            this.timer = data.timer || 60;
                            this.resendText = "Resend Code";
                            this.startTimer();

                            this.$nextTick(() => {
                                if (this.$refs['otp0']) {
                                    this.$refs['otp0'].focus();
                                }
                            });
                        } else {
                            this.verificationStatus = {
                                type: 'error',
                                message: data.message || 'Failed to resend OTP.'
                            };
                            this.resendText = "Resend Code";
                        }
                    })
                    .catch(() => {
                        this.verificationStatus = {
                            type: 'error',
                            message: 'Failed to resend OTP.'
                        };
                        this.resendText = "Resend Code";
                    });
                }
            }
        }
    </script>
@endsection