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
@endphp

@extends('layouts.frontend')

@section('content')
<section class="bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950 min-h-screen pb-16 overflow-x-hidden">
    <div class="max-w-7xl mx-auto pt-4 md:pt-8 px-3 sm:px-4 md:px-8">
        <!-- Signup Container -->
        <div class="bg-gray-900/90 rounded-2xl md:rounded-3xl shadow-2xl border border-gray-800 overflow-hidden">
            <div class="flex flex-col lg:flex-row">
                <!-- Left Side - Signup Form -->
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

                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">
                            Create Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500">Account</span>
                        </h1>
                        <p class="text-gray-400">Join 500+ verified babes and start earning today</p>
                    </div>

                    <!-- Free Trial Badge -->
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl p-4 mb-8 flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 019.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">21 Days Free Advertising</h3>
                            <p class="text-white/80 text-sm">No credit card required • No obligations</p>
                        </div>
                    </div>

                    <!-- Features Grid -->
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Rank #1 on Google</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Unlimited photos/videos</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Twitter promotions</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Profile booster</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>From $0.79/day</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Free when hidden</span>
                        </div>
                    </div>

                    <!-- Signup Form -->
                    <form class="space-y-5" x-data="signupForm()" @submit.prevent="submitForm">
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-envelope mr-2 text-pink-400"></i>Your email address
                            </label>
                            <input type="email"
                                   x-model="form.email"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                   placeholder="Enter your email">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2 text-pink-400"></i>Choose your password
                            </label>
                            <input type="password"
                                   x-model="form.password"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                   placeholder="**********">
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>8-20 characters, letters and numbers recommended
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2 text-pink-400"></i>Retype your password
                            </label>
                            <input type="password"
                                   x-model="form.password_confirmation"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                   placeholder="**********">
                        </div>

                        <!-- Nickname -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-user mr-2 text-pink-400"></i>Your preferred (nick) name
                            </label>
                            <input type="text"
                                   x-model="form.nickname"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                   placeholder="e.g., SexyBabe">
                        </div>

                        <!-- Mobile Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-phone mr-2 text-pink-400"></i>Your mobile number
                            </label>
                            <div class="flex gap-2">
                                <select x-model="form.country_code"
                                        class="w-24 px-3 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="+61">🇦🇺 +61</option>
                                    <option value="+64">🇳🇿 +64</option>
                                    <option value="+44">🇬🇧 +44</option>
                                </select>
                                <input type="tel"
                                       x-model="form.phone"
                                       class="flex-1 px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                       placeholder="4XX XXX XXX">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-shield-alt mr-1"></i>We'll call to verify you. Never shared.
                            </p>
                        </div>

                        <!-- Suburb -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-map-marker-alt mr-2 text-pink-400"></i>Suburb (main work location)
                            </label>
                            <input type="text"
                                   x-model="form.suburb"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                   placeholder="Start typing your suburb...">
                        </div>

                        <!-- Referral Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-gift mr-2 text-pink-400"></i>Friend's referral code (optional)
                            </label>
                            <input type="text"
                                   x-model="form.referral"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                   placeholder="Leave blank if none">
                        </div>

                        <!-- Age Confirmation -->
                        <div class="flex items-center gap-3">
                            <input type="checkbox"
                                   x-model="form.age_confirm"
                                   id="ageConfirm"
                                   class="w-5 h-5 bg-gray-800 border-gray-700 rounded text-purple-500 focus:ring-purple-500">
                            <label for="ageConfirm" class="text-sm text-gray-300">
                                I confirm that I am 18+ years old
                            </label>
                        </div>

                        <!-- reCAPTCHA -->
                        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4 flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                <i class="fab fa-google text-blue-400 text-xl"></i>
                            </div>
                            <span class="text-sm text-gray-300">I'm not a robot</span>
                            <span class="text-xs text-gray-500 ml-auto">reCAPTCHA</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                                :disabled="!form.age_confirm"
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-pink-600 hover:to-purple-600 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <span>YES SIGN ME UP</span>
                        </button>

                        <!-- Footer Note -->
                        <p class="text-center text-gray-500 text-sm">
                            <i class="fas fa-heart text-pink-500"></i>
                            Put on your naughty shoes and join RealBabes today
                            <i class="fas fa-heart text-pink-500"></i>
                        </p>
                    </form>
                </div>

                <!-- Right Side - Slider (FIXED) -->
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

                            <!-- Navigation Arrows - FIXED: Now properly positioned inside the slider -->
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

<!-- Alpine.js Component -->
<script>
    function signupForm() {
        return {
            form: {
                email: '',
                password: '',
                password_confirmation: '',
                nickname: '',
                country_code: '+61',
                phone: '',
                suburb: '',
                referral: '',
                age_confirm: true
            },
            submitForm() {
                // Handle form submission
                console.log('Form submitted:', this.form);
                alert('Registration submitted! (Demo)');
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
/* Custom styles for the signup page */
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
</style>
@endsection
