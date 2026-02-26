@extends('layouts.frontend')

@section('content')
<!-- ================= TOP SIGNUP BANNER ================= -->
<div style="width:100%; background:#b784a7;">
    <div style="display:flex; width:100%; height:350px; overflow:hidden;">
        <!-- LEFT IMAGE -->
        <div style="
            flex:1;
            background:url('https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?q=80&w=1200&auto=format&fit=crop') center center/cover no-repeat;
            position:relative;">
            <div style="position:absolute; inset:0; background:rgba(92,53,89,0.6);"></div>
        </div>
        <!-- CENTER LOGO TEXT -->
        <div style="flex:1; background:#c893b8; display:flex; align-items:center; justify-content:center; flex-direction:column; text-align:center;">
            <h2 style="margin:0; font-size:40px; font-weight:700; color:#000;">
                hotescorts.com.au
            </h2>
            <span style="font-size:12px; letter-spacing:2px; color:#333;">
                REAL WOMEN NEAR YOU
            </span>
        </div>
        <!-- RIGHT IMAGE -->
        <div style="
            flex:1;
            background:url('https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop') center center/cover no-repeat;
            position:relative;">
            <div style="position:absolute; inset:0; background:rgba(92,53,89,0.6);"></div>
        </div>
    </div>
</div>
<!-- ================= END BANNER ================= -->

<!-- Main Content -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px 20px 20px;">

        <!-- OTP Verification Header -->
        <h1 style="font-size: 2.5rem; font-weight: 700; color: #222; margin-bottom: 5px; border-left: 5px solid #e04ecb; padding-left: 15px;">
            Verify Your <span style="color: #e04ecb;">Number</span>
        </h1>

        <!-- Description -->
        <div style="margin: 25px 0 20px 0;">
            <p style="font-size: 1.1rem; color: #555; margin-bottom: 10px;">
                <i class="fas fa-shield-alt" style="color: #e04ecb; margin-right: 8px;"></i>
                We've sent a verification code to your phone. Please enter it below.
            </p>
        </div>

        <hr style="border: none; border-top: 2px solid #f0f0f0; margin: 25px 0;">

        <!-- OTP Verification Form -->
        <div style="background: #ffffff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 35px 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">

            <!-- Phone Number Display -->
            <div style="background: #f8f8f8; border: 1px solid #e0e0e0; border-radius: 10px; padding: 15px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 45px; height: 45px; background: #e04ecb; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-phone-alt" style="color: white; font-size: 18px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #888; margin-bottom: 3px;">Verifying number</div>
                        <div style="font-size: 1.2rem; font-weight: 600; color: #333;">+61 412 345 678</div>
                    </div>
                </div>
                <a href="#" style="color: #e04ecb; font-weight: 500; text-decoration: none; font-size: 0.95rem;">
                    Change <i class="fas fa-chevron-right" style="font-size: 12px; margin-left: 5px;"></i>
                </a>
            </div>

            <div x-data="otpVerification()" x-init="init()">
                <form @submit.prevent="verifyOTP">
                    <!-- OTP Input Fields -->
                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-weight: 600; color: #333; margin-bottom: 15px;">
                            <i class="fas fa-lock" style="color: #e04ecb; margin-right: 8px;"></i>
                            Enter 6-digit verification code
                        </label>

                        <!-- OTP Digits - FIXED VERSION -->
                        <div style="display: flex; gap: 10px; justify-content: space-between; margin-bottom: 20px;">
                            <template x-for="(digit, index) in 6" :key="index">
                                <input type="text"
                                       x-model="otpDigits[index]"
                                       :ref="'otpInput' + index"
                                       @input="handleInput(index, $event)"
                                       @keydown="handleKeydown(index, $event)"
                                       @paste="handlePaste"
                                       @focus="focusedIndex = index"
                                       maxlength="1"
                                       style="width: 60px; height: 70px; text-align: center; font-size: 24px; font-weight: 700; background: #f9f9f9; border: 2px solid #e0e0e0; border-radius: 10px; color: #333; outline: none; transition: all 0.3s;"
                                       :style="focusedIndex === index ? 'border-color: #e04ecb; box-shadow: 0 0 0 3px rgba(224,78,203,0.1);' : ''">
                            </template>
                        </div>

                        <!-- Timer and Resend -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 15px;">
                            <div style="display: flex; align-items: center; gap: 8px; color: #666; font-size: 0.95rem;">
                                <i class="far fa-clock" style="color: #e04ecb;"></i>
                                <span x-text="timerText"></span>
                            </div>
                            <button type="button"
                                    @click="resendOTP"
                                    :disabled="!canResend"
                                    style="background: none; border: none; color: #e04ecb; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: all 0.3s;"
                                    :style="!canResend ? 'opacity: 0.5; cursor: not-allowed;' : ''"
                                    x-text="resendText">
                            </button>
                        </div>
                    </div>

                    <!-- Verification Status Message -->
                    <div x-show="verificationStatus"
                         x-transition
                         style="padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;"
                         :style="verificationStatus?.type === 'success' ? 'background: #e6f7e6; border: 1px solid #a5d6a5;' : 'background: #fee; border: 1px solid #fcc;'">
                        <i :class="verificationStatus?.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"
                           :style="verificationStatus?.type === 'success' ? 'color: #4caf50;' : 'color: #f44336;'"></i>
                        <span :style="verificationStatus?.type === 'success' ? 'color: #2e7d32;' : 'color: #c62828;'"
                              x-text="verificationStatus?.message"></span>
                    </div>

                    <!-- Verification Info Box - Matching signup page style -->
                    <div style="background: #e8f0fe; border-left: 4px solid #e04ecb; border-radius: 6px; padding: 15px; font-size: 0.95rem; color: #333; margin: 25px 0;">
                        <div style="display: flex; gap: 10px;">
                            <i class="fas fa-info-circle" style="color: #e04ecb; font-size: 20px;"></i>
                            <div>
                                <span style="font-weight: 600;">Why we verify your number?</span> We verify all our babes to ensure authenticity. This helps us maintain a safe community of real babes only. <span style="font-weight: 600;">We will NEVER publish or share this phone number without your permission.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button - FIXED VISIBILITY -->
                    <button type="submit"
                            :disabled="!isOtpComplete || isVerifying"
                            style="width: 100%; background: linear-gradient(135deg, #e04ecb 0%, #c13ab0 100%); color: white; font-weight: 700; font-size: 1.3rem; padding: 16px 0; border: none; border-radius: 50px; cursor: pointer; transition: all 0.3s; box-shadow: 0 5px 15px rgba(224,78,203,0.3); display: flex; align-items: center; justify-content: center; gap: 10px;"
                            :style="!isOtpComplete || isVerifying ? 'opacity: 0.6; cursor: not-allowed;' : ''"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(224,78,203,0.4)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(224,78,203,0.3)';">
                        <i class="fas fa-check-circle" x-show="!isVerifying"></i>
                        <i class="fas fa-spinner fa-spin" x-show="isVerifying" style="display: none;"></i>
                        <span x-text="isVerifying ? 'Verifying...' : 'VERIFY & CONTINUE'"></span>
                    </button>
                </form>

                <!-- Help Text -->
                <p style="text-align: center; margin-top: 25px; color: #888; font-size: 0.9rem;">
                    <i class="fas fa-question-circle" style="color: #e04ecb; margin-right: 5px;"></i>
                    Didn't receive the code?
                    <button type="button" @click="showHelp" style="background: none; border: none; color: #e04ecb; font-weight: 600; cursor: pointer; text-decoration: underline;">
                        Contact support
                    </button>
                </p>

                <!-- Back to Signup Link -->
                <div style="text-align: center; margin-top: 20px;">
                    <a href="#" style="color: #666; text-decoration: none; font-size: 0.95rem; transition: all 0.3s;">
                        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
                        Back to Signup
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <p style="text-align: center; color: #999; font-size: 0.9rem; margin-top: 30px;">
            <i class="fas fa-heart" style="color: #e04ecb;"></i>
            Your privacy and security are our top priorities
            <i class="fas fa-heart" style="color: #e04ecb;"></i>
        </p>
    </div>
</div>

<!-- Alpine.js Component for OTP Verification -->
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
                // Auto-focus first input
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
                    // Demo: Accept any 6-digit code or specifically "123456" for testing
                    if (this.otpCode === '123456') {
                        this.verificationStatus = {
                            type: 'success',
                            message: '✓ Phone number verified successfully! Redirecting...'
                        };

                        // Simulate redirect after success
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

                // Simulate resend API call
                setTimeout(() => {
                    this.startTimer();
                    this.verificationStatus = {
                        type: 'success',
                        message: '✓ New code sent to your phone!'
                    };

                    // Clear success message after 3 seconds
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

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Global Styles */
body, html {
    overflow-x: hidden !important;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Responsive Design */
@media (max-width: 900px) {
    div[style*="max-width: 800px"] {
        padding: 20px 15px !important;
    }

    div[style*="display: flex"][style*="gap: 10px"][style*="justify-content: space-between"] {
        flex-wrap: wrap !important;
        justify-content: center !important;
    }

    input[style*="width: 60px"] {
        width: 45px !important;
        height: 55px !important;
        font-size: 20px !important;
    }

    h1 {
        font-size: 2rem !important;
    }

    [style*="border-radius: 12px"] {
        padding: 25px 20px !important;
    }
}

@media (max-width: 768px) {
    div[style*="height:350px"] {
        height: 250px !important;
    }

    div[style*="font-size:40px"] {
        font-size: 28px !important;
    }

    input[style*="width: 60px"] {
        width: 40px !important;
        height: 50px !important;
        font-size: 18px !important;
    }
}

/* OTP input spin button removal */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

/* Hover Effects */
input:hover {
    border-color: #e04ecb !important;
}

/* Smooth Transitions */
input, button {
    transition: all 0.3s ease;
}

/* Focus States */
input:focus {
    outline: none;
    border-color: #e04ecb;
    box-shadow: 0 0 0 3px rgba(224,78,203,0.1);
}

/* Button hover effect */
button[type="submit"]:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(224,78,203,0.4);
}

/* Link hover effect */
a:hover {
    color: #e04ecb !important;
}
</style>
@endsection
