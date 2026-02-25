@php
// Prevent horizontal scroll on html/body at all zoom levels
echo '<style>html,body{overflow-x:hidden!important;}</style>';

// Dummy data for slider
$sliderModels = [
    [
        'name' => 'Sophia Rose',
        'location' => 'Sydney',
        'price' => '$180',
        'image' => 'https://images.unsplash.com/photo-1494790108777-467efef4493f?w=600&h=800&fit=crop',
        'tags' => ['VIP', 'LIVE', 'HD'],
        'online' => true,
    ],
    [
        'name' => 'Isabella Marie',
        'location' => 'Melbourne',
        'price' => '$250',
        'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&h=800&fit=crop',
        'tags' => ['TOP', '4K', 'NEW'],
        'online' => true,
    ],
    [
        'name' => 'Mia Johnson',
        'location' => 'Brisbane',
        'price' => '$200',
        'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&h=800&fit=crop',
        'tags' => ['PREMIUM', 'VERIFIED'],
        'online' => false,
    ],
    [
        'name' => 'Emma Wilson',
        'location' => 'Perth',
        'price' => '$160',
        'image' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=600&h=800&fit=crop',
        'tags' => ['NEW', 'HD'],
        'online' => true,
    ],
    [
        'name' => 'Olivia Brown',
        'location' => 'Gold Coast',
        'price' => '$300',
        'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=600&h=800&fit=crop',
        'tags' => ['VIP', 'TOP'],
        'online' => true,
    ],
];

// Stats data
$stats = [
    ['number' => '500+', 'label' => 'Active Models'],
    ['number' => '50K+', 'label' => 'Happy Clients'],
    ['number' => '24/7', 'label' => 'Support'],
];

// Simulated phone number from previous step
$phoneNumber = '+61 412 345 678';
@endphp

@extends('layouts.frontend')

@section('content')
<section class="bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950 min-h-screen pb-16 overflow-x-hidden">
    <div class="max-w-7xl mx-auto pt-4 md:pt-8 px-3 sm:px-4 md:px-8">
        <!-- OTP Verification Container -->
        <div class="bg-gray-900/90 rounded-2xl md:rounded-3xl shadow-2xl border border-gray-800 overflow-hidden">
            <div class="flex flex-col lg:flex-row">
                <!-- Left Side - OTP Verification Form -->
                <div class="lg:w-3/5 p-6 md:p-8 lg:p-10">
                    <!-- Logo -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-2xl font-bold text-white">RealBabes</span>
                            <span class="text-xs text-gray-400 block">Australia's Premier Directory</span>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mb-6">
                        {{-- <a href="{{ route('signup') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition"> --}}

                            <a href="#" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Back to Signup</span>
                        </a>
                    </div>

                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">
                                    Verify Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500">Number</span>
                                </h1>
                                <p class="text-gray-400">We've sent a verification code to your phone</p>
                            </div>
                        </div>
                    </div>

                    <!-- Phone Number Display -->
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4 mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-phone-alt text-green-400"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Verifying number</p>
                                <p class="text-white font-semibold">{{ $phoneNumber }}</p>
                            </div>
                        </div>
                         <a href="#" class="text-pink-400 hover:text-pink-300 text-sm font-medium">
                        {{-- <a href="{{ route('signup') }}" class="text-pink-400 hover:text-pink-300 text-sm font-medium"> --}}
                            Change
                        </a>
                    </div>

                    <!-- OTP Form -->
                    <form class="space-y-6" x-data="otpVerification()" x-init="init()" @submit.prevent="verifyOTP">
                        <!-- OTP Input Fields -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">
                                <i class="fas fa-lock mr-2 text-pink-400"></i>Enter 6-digit verification code
                            </label>

                            <!-- OTP Digits -->
                            <div class="flex gap-2 justify-between mb-4">
                                <template x-for="(digit, index) in 6" :key="index">
                                    <input type="text"
                                           x-model="otpDigits[index]"
                                           x-ref="'otpInput' + index"
                                           @input="handleInput(index, $event)"
                                           @keydown="handleKeydown(index, $event)"
                                           @paste="handlePaste"
                                           @focus="focusedIndex = index"
                                           maxlength="1"
                                           class="w-12 h-14 text-center text-xl font-bold bg-gray-800 border-2 border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                           :class="{ 'border-pink-500 ring-2 ring-pink-500': focusedIndex === index }">
                                </template>
                            </div>

                            <!-- Timer and Resend -->
                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-gray-400" x-text="timerText"></span>
                                </div>
                                <button type="button"
                                        @click="resendOTP"
                                        :disabled="!canResend"
                                        class="text-pink-400 hover:text-pink-300 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition"
                                        x-text="resendText">
                                </button>
                            </div>
                        </div>

                        <!-- Verification Status -->
                        <div x-show="verificationStatus"
                             x-transition
                             class="p-4 rounded-xl"
                             :class="verificationStatus?.type === 'success' ? 'bg-green-500/20 border border-green-500/30' : 'bg-red-500/20 border border-red-500/30'">
                            <div class="flex items-center gap-3">
                                <i :class="verificationStatus?.type === 'success' ? 'fas fa-check-circle text-green-400' : 'fas fa-exclamation-circle text-red-400'"></i>
                                <span class="text-sm" :class="verificationStatus?.type === 'success' ? 'text-green-400' : 'text-red-400'" x-text="verificationStatus?.message"></span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                                :disabled="!isOtpComplete || isVerifying"
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-pink-600 hover:to-purple-600 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle" x-show="!isVerifying"></i>
                            <svg x-show="isVerifying" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isVerifying ? 'Verifying...' : 'VERIFY & CONTINUE'"></span>
                        </button>

                        <!-- Help Text -->
                        <p class="text-center text-gray-500 text-sm">
                            <i class="fas fa-question-circle mr-1"></i>
                            Didn't receive the code? Check your spam or
                            <button type="button" @click="showHelp" class="text-pink-400 hover:text-pink-300">
                                contact support
                            </button>
                        </p>
                    </form>

                    <!-- Footer Note -->
                    <p class="text-center text-gray-500 text-sm mt-8">
                        <i class="fas fa-heart text-pink-500"></i>
                        Your privacy and security are our top priorities
                        <i class="fas fa-heart text-pink-500"></i>
                    </p>
                </div>

                <!-- Right Side - Slider (Same as signup page) -->
                <div class="lg:w-2/5 bg-gradient-to-br from-purple-900/50 to-pink-900/50 p-6 md:p-8 lg:p-10 flex flex-col">
                    <!-- Slider Header -->
                    <div class="mb-8">
                        <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                            Featured <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-pink-400">Models</span>
                        </h2>
                        <p class="text-gray-300">Join 500+ verified babes today</p>
                    </div>

                    <!-- Slider Container -->
                    <div class="relative flex-1" x-data="slider()" x-init="init()">
                        <!-- Slider -->
                        <div class="relative h-[400px] rounded-2xl overflow-hidden group">
                            <template x-for="(slide, index) in slides" :key="index">
                                <div x-show="currentSlide === index"
                                     x-transition:enter="transition ease-out duration-500"
                                     x-transition:enter-start="opacity-0 scale-105"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="absolute inset-0">
                                    <!-- Background Image -->
                                    <div class="absolute inset-0 bg-cover bg-center"
                                         :style="{ backgroundImage: `linear-gradient(to top, rgba(0,0,0,0.8), transparent), url(${slide.image})` }">
                                    </div>

                                    <!-- Content -->
                                    <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span x-show="slide.online"
                                                  class="bg-green-500/20 text-green-400 text-xs px-2 py-1 rounded-full border border-green-500/30 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                                LIVE NOW
                                            </span>
                                        </div>
                                        <h3 class="text-2xl font-bold text-white mb-1" x-text="slide.name"></h3>
                                        <p class="text-gray-300 text-sm mb-3 flex items-center gap-1">
                                            <svg class="w-4 h-4 text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span x-text="slide.location"></span>
                                        </p>
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            <template x-for="tag in slide.tags" :key="tag">
                                                <span class="bg-purple-600/80 text-white text-xs px-3 py-1 rounded-full border border-purple-400/30">
                                                    <span x-text="tag"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-pink-400 font-bold text-xl" x-text="slide.price"></span>
                                            <button class="bg-gradient-to-r from-pink-500 to-purple-600 text-white text-sm px-4 py-2 rounded-lg hover:shadow-lg transition transform hover:scale-105">
                                                View Profile
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Navigation Arrows -->
                            <button @click="prevSlide"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 backdrop-blur-sm transition opacity-0 group-hover:opacity-100 z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button @click="nextSlide"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 backdrop-blur-sm transition opacity-0 group-hover:opacity-100 z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Navigation Dots -->
                        <div class="flex justify-center gap-2 mt-4">
                            <template x-for="(slide, index) in slides" :key="index">
                                <button @click="currentSlide = index"
                                        class="h-2 rounded-full transition-all duration-300"
                                        :class="currentSlide === index ? 'w-6 bg-pink-500' : 'w-2 bg-white/30 hover:bg-white/50'">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-white/10">
                        @foreach($stats as $stat)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ $stat['number'] }}</div>
                            <div class="text-xs text-gray-400">{{ $stat['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Alpine.js Component for OTP Verification -->
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
                    if (this.$refs.otpInput0) {
                        this.$refs.otpInput0.focus();
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
                        if (nextInput) nextInput.focus();
                    });
                }
            },

            handleKeydown(index, event) {
                // Handle backspace
                if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                    this.focusedIndex = index - 1;
                    this.$nextTick(() => {
                        const prevInput = this.$refs['otpInput' + (index - 1)];
                        if (prevInput) prevInput.focus();
                    });
                }

                // Handle left arrow
                if (event.key === 'ArrowLeft' && index > 0) {
                    this.focusedIndex = index - 1;
                    this.$nextTick(() => {
                        const prevInput = this.$refs['otpInput' + (index - 1)];
                        if (prevInput) prevInput.focus();
                    });
                }

                // Handle right arrow
                if (event.key === 'ArrowRight' && index < 5) {
                    this.focusedIndex = index + 1;
                    this.$nextTick(() => {
                        const nextInput = this.$refs['otpInput' + (index + 1)];
                        if (nextInput) nextInput.focus();
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
                        if (nextInput) nextInput.focus();
                    });
                } else {
                    this.focusedIndex = 5;
                    this.$nextTick(() => {
                        const lastInput = this.$refs.otpInput5;
                        if (lastInput) lastInput.focus();
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

                        // Clear OTP fields on error (optional)
                        // this.otpDigits = ['', '', '', '', '', ''];
                        // this.focusedIndex = 0;
                        // this.$nextTick(() => {
                        //     if (this.$refs.otpInput0) this.$refs.otpInput0.focus();
                        // });
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
                alert('Please contact support at support@realbabes.com.au or call 1800 123 456');
            }
        }
    }

    function slider() {
        return {
            currentSlide: 0,
            slides: @json($sliderModels),
            interval: null,

            init() {
                this.startAutoplay();
            },

            startAutoplay() {
                this.interval = setInterval(() => {
                    this.nextSlide();
                }, 5000);
            },

            stopAutoplay() {
                clearInterval(this.interval);
            },

            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.slides.length;
            },

            prevSlide() {
                this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
            },

            goToSlide(index) {
                this.currentSlide = index;
            }
        }
    }
</script>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Custom styles for the OTP page */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

/* Smooth transitions */
* {
    -webkit-tap-highlight-color: transparent;
}

/* Custom scrollbar */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #a78bfa;
    border-radius: 20px;
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

/* Focus ring animation */
@keyframes focusPulse {
    0%, 100% { box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.5); }
    50% { box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.3); }
}

.focus-ring-pulse:focus {
    animation: focusPulse 1.5s infinite;
}
</style>
@endsection
