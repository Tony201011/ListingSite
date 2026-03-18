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

                    <!-- OTP INPUTS -->
                    <div class="flex justify-center gap-2 mb-6">
                        <template x-for="(digit, index) in otpDigits" :key="index">
                            <input
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="1"
                                autocomplete="one-time-code"
                                :value="otpDigits[index]"
                                @input="handleInput(index, $event)"
                                @keydown.backspace="handleBackspace(index, $event)"
                                @keydown.arrow-left.prevent="focusInput(index - 1)"
                                @keydown.arrow-right.prevent="focusInput(index + 1)"
                                @paste="handlePaste($event)"
                                class="otp-input w-14 h-14 text-center text-2xl font-extrabold text-black bg-white border-2 border-gray-400 rounded-xl focus:border-[#e04ecb] focus:outline-none shadow-sm"
                            >
                        </template>
                    </div>

                    <!-- TIMER + RESEND -->
                    <div class="flex justify-between text-sm mb-6">
                        <span x-text="timerText" class="text-lg font-bold text-black bg-yellow-100 px-3 py-1 rounded"></span>

                        <button
                            type="button"
                            @click="resendOTP"
                            :disabled="!canResend"
                            class="bg-transparent border-0 text-[#e04ecb] font-semibold disabled:opacity-50"
                        >
                            <span x-text="resendText"></span>
                        </button>
                    </div>

                    <!-- STATUS -->
                    <div
                        x-show="verificationStatus"
                        x-transition
                        class="p-4 rounded-xl mb-6"
                        :class="verificationStatus && verificationStatus.type === 'success'
                            ? 'bg-green-50 text-green-700'
                            : 'bg-red-50 text-red-700'"
                    >
                        <span x-text="verificationStatus?.message"></span>
                    </div>

                    <!-- SUBMIT -->
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

            this.$nextTick(() => this.focusInput(0));
        },

        getInputs() {
            return this.$root.querySelectorAll('.otp-input');
        },

        focusInput(index) {
            const inputs = this.getInputs();
            if (index >= 0 && index < inputs.length) {
                inputs[index].focus();
                inputs[index].select();
            }
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

            const m = Math.floor(this.timer / 60);
            const s = this.timer % 60;
            this.timerText = `Code expires in ${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
        },

        startTimer() {
            if (this.intervalId) clearInterval(this.intervalId);

            this.canResend = false;
            this.updateTimerText();

            this.intervalId = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                    this.updateTimerText();
                } else {
                    clearInterval(this.intervalId);
                    this.canResend = true;
                    this.timerText = 'Code expired';
                }
            }, 1000);
        },

        handleInput(index, event) {
            const value = event.target.value.replace(/\D/g, '').slice(0,1);

            this.otpDigits[index] = value;
            event.target.value = value;

            if (value !== '' && index < this.otpDigits.length - 1) {
                setTimeout(() => this.focusInput(index + 1), 0);
            }
        },

        handleBackspace(index, event) {
            if (event.target.value !== '' || this.otpDigits[index] !== '') {
                this.otpDigits[index] = '';
                event.target.value = '';
                return;
            }

            if (index > 0) {
                setTimeout(() => {
                    this.focusInput(index - 1);
                    const inputs = this.getInputs();
                    this.otpDigits[index - 1] = '';
                    inputs[index - 1].value = '';
                }, 0);
            }
        },

        handlePaste(event) {
            event.preventDefault();

            const pasted = event.clipboardData.getData('text')
                .replace(/\D/g, '')
                .slice(0,6);

            for (let i = 0; i < 6; i++) {
                this.otpDigits[i] = pasted[i] || '';
            }

            this.$nextTick(() => {
                this.focusInput(pasted.length >= 6 ? 5 : pasted.length);
            });
        },

        async verifyOTP() {
            if (!this.isOtpComplete || this.isVerifying) return;

            this.isVerifying = true;

            try {
                const res = await fetch("{{ route('verify.otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ otp: this.otpCode })
                });

                const data = await res.json();

                if (data.success) {
                    window.location.href = data.redirect || '/dashboard';
                } else {
                    this.verificationStatus = { type: 'error', message: data.message };
                    this.isVerifying = false;
                }
            } catch {
                this.verificationStatus = { type: 'error', message: 'Error verifying OTP' };
                this.isVerifying = false;
            }
        },

        async resendOTP() {
            if (!this.canResend) return;

            this.resendText = "Sending...";

            try {
                const res = await fetch("{{ route('resend.otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();

                if (data.success) {
                    this.otpDigits = ['', '', '', '', '', ''];
                    this.timer = data.timer || 60;
                    this.startTimer();
                    this.focusInput(0);
                }

                this.resendText = "Resend Code";
            } catch {
                this.resendText = "Resend Code";
            }
        }
    }
}
</script>
@endsection
