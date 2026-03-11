@extends('layouts.frontend')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="bg-[#f8fafc] min-h-screen py-10">
        <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

            <a href="javascript:history.back()"
                class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] mb-4 text-sm font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
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

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-8 flex justify-between">

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

                        <input type="hidden" x-model="otpCode" name="otp">

                        <div class="flex justify-center gap-2 mb-6">

                            <template x-for="(digit,index) in 6">

                                <input type="text" maxlength="1" x-model="otpDigits[index]" :ref="'otp' + index"
                                    @input="handleInput(index,$event)"
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-200 rounded-xl focus:border-[#e04ecb]">

                            </template>

                        </div>


                        <div class="flex justify-between text-sm mb-6">

                            <span x-text="timerText"></span>

                            <button type="button" @click="resendOTP" :disabled="!canResend"
                                class="text-[#e04ecb] font-semibold disabled:opacity-50">

                                <span x-text="resendText"></span>

                            </button>

                        </div>


                        <div x-show="verificationStatus" class="p-4 rounded-xl mb-6"
                            :class="verificationStatus.type == 'success' ? 'bg-green-50 text-green-700' :
                                'bg-red-50 text-red-700'">

                            <span x-text="verificationStatus.message"></span>

                        </div>


                        <button type="submit" :disabled="!isOtpComplete || isVerifying"
                            class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold py-4 rounded-full">

                            <span x-text="isVerifying ? 'Verifying...' : 'VERIFY & CONTINUE'"></span>

                        </button>

                    </form>

                </div>

            </div>

        </div>
    </div>


    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        window.otpTimer = {{ $remainingTime ?? 60 }};
    </script>

    <script>
        function otpVerification() {

            return {

                otpDigits: ['', '', '', '', '', ''],

                timer: 60,
                canResend: false,
                resendText: 'Resend Code',
                timerText: 'Code expires in 01:00',

                isVerifying: false,
                verificationStatus: null,


                init() {

                    this.startTimer()

                    this.$nextTick(() => {

                        this.$refs.otp0.focus()

                    })

                },


                startTimer() {

                    this.timer = window.otpTimer
                    this.canResend = false
                    const interval = setInterval(() => {

                        if (this.timer > 0) {

                            this.timer--

                            let m = Math.floor(this.timer / 60)
                            let s = this.timer % 60

                            this.timerText =
                                `Code expires in ${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`

                        } else {

                            this.canResend = true
                            this.resendText = 'Resend Code'
                            clearInterval(interval)

                        }

                    }, 1000)

                },


                handleInput(index, event) {

                    if (event.target.value && index < 5) {

                        this.$refs['otp' + (index + 1)].focus()

                    }

                },


                get otpCode() {

                    return this.otpDigits.join('')

                },

                get isOtpComplete() {

                    return this.otpDigits.every(d => d != '')

                },



                verifyOTP() {

                    if (!this.isOtpComplete) return

                    this.isVerifying = true

                    fetch("{{ route('verify.otp') }}", {

                            method: "POST",

                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },

                            body: JSON.stringify({
                                otp: this.otpCode
                            })

                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.success) {

                                this.verificationStatus = {
                                    type: 'success',
                                    message: 'Phone verified successfully'
                                }

                                setTimeout(() => {
                                    window.location.href = data.redirect
                                }, 1500)

                            } else {

                                this.verificationStatus = {
                                    type: 'error',
                                    message: data.message
                                }

                                this.isVerifying = false

                            }

                        })

                        .catch(() => {

                            this.verificationStatus = {
                                type: 'error',
                                message: 'Server error'
                            }

                            this.isVerifying = false

                        })

                },



                resendOTP() {

                    if (!this.canResend) return

                    this.resendText = 'Sending...'

                    fetch("{{ route('resend.otp') }}", {

                            method: "POST",

                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }

                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.success) {

                                this.startTimer()

                                this.verificationStatus = {
                                    type: 'success',
                                    message: 'OTP resent successfully'
                                }

                            } else {

                                this.verificationStatus = {
                                    type: 'error',
                                    message: data.message
                                }

                            }

                        })

                }

            }

        }
    </script>
@endsection
