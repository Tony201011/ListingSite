@php
// Prevent horizontal scroll on html/body at all zoom levels
echo '<style>html,body{overflow-x:hidden!important;}</style>';

// Dummy data for featured escorts (top performers)
$featuredEscorts = [
    [
        'name' => 'Sophia Rose',
        'age' => 24,
        'location' => 'New York',
        'rating' => 4.9,
        'views' => 1245,
        'image' => 'https://images.unsplash.com/photo-1494790108777-467efef4493f?w=600&h=800&fit=crop',
        'tags' => ['VIP', 'HD', 'NEW'],
        'online' => true,
        'country' => '🇺🇸'
    ],
    [
        'name' => 'Isabella Marie',
        'age' => 26,
        'location' => 'Los Angeles',
        'rating' => 4.8,
        'views' => 987,
        'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&h=800&fit=crop',
        'tags' => ['TOP', 'HD'],
        'online' => true,
        'country' => '🇺🇸'
    ],
    [
        'name' => 'Mia Johnson',
        'age' => 23,
        'location' => 'Miami',
        'rating' => 4.9,
        'views' => 1567,
        'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&h=800&fit=crop',
        'tags' => ['VIP', '4K'],
        'online' => false,
        'country' => '🇺🇸'
    ],
    [
        'name' => 'Emma Wilson',
        'age' => 25,
        'location' => 'Chicago',
        'rating' => 4.7,
        'views' => 876,
        'image' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=600&h=800&fit=crop',
        'tags' => ['NEW', 'HD'],
        'online' => true,
        'country' => '🇺🇸'
    ],
    [
        'name' => 'Olivia Brown',
        'age' => 27,
        'location' => 'Houston',
        'rating' => 4.9,
        'views' => 2134,
        'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=600&h=800&fit=crop',
        'tags' => ['TOP', 'VIP'],
        'online' => true,
        'country' => '🇺🇸'
    ],
];

// Dummy data for latest escorts from Australia (matching the image design)
$latestEscorts = [
    [
        'name' => 'Dixie Laveaux',
        'price' => 180,
        'location' => 'Bondi Junction',
        'type' => 'Escort',
        'time_ago' => '6 minutes ago',
        'verified' => true,
        'featured' => true,
        'image' => 'https://images.unsplash.com/photo-1494790108777-467efef4493f?w=400&h=500&fit=crop',
    ],
    [
        'name' => 'Queen Alexa',
        'price' => 500,
        'location' => 'Melbourne',
        'type' => 'Escort',
        'time_ago' => '6 minutes ago',
        'verified' => true,
        'featured' => true,
        'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&h=500&fit=crop',
    ],
    [
        'name' => 'Butt Plug in my Ass',
        'price' => 300,
        'location' => 'Pennant Hills',
        'type' => 'Escort',
        'time_ago' => '7 minutes ago',
        'verified' => true,
        'featured' => true,
        'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=500&fit=crop',
    ],
    [
        'name' => 'Players of Pleasure',
        'price' => 250,
        'location' => 'Dee Why',
        'type' => 'Escort',
        'time_ago' => '8 minutes ago',
        'verified' => true,
        'featured' => true,
        'image' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&h=500&fit=crop',
    ],
    [
        'name' => 'Naughty Kitty69',
        'price' => 250,
        'location' => 'Craigieburn',
        'type' => 'Escort',
        'time_ago' => '8 minutes ago',
        'verified' => true,
        'featured' => true,
        'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&h=500&fit=crop',
    ],
    [
        'name' => 'Scarlet Rose',
        'price' => 350,
        'location' => 'Sydney',
        'type' => 'Escort',
        'time_ago' => '9 minutes ago',
        'verified' => true,
        'featured' => false,
        'image' => 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=400&h=500&fit=crop',
    ],
    [
        'name' => 'Mia Sapphire',
        'price' => 220,
        'location' => 'Brisbane',
        'type' => 'Escort',
        'time_ago' => '10 minutes ago',
        'verified' => false,
        'featured' => true,
        'image' => 'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?w=400&h=500&fit=crop',
    ],
    [
        'name' => 'Luna Night',
        'price' => 280,
        'location' => 'Perth',
        'type' => 'Escort',
        'time_ago' => '12 minutes ago',
        'verified' => true,
        'featured' => false,
        'image' => 'https://images.unsplash.com/photo-1464863979621-258859e62245?w=400&h=500&fit=crop',
    ],
];

// Dummy data for top escorts grid
$dummyTopEscorts = [
    ['name' => 'Sophia Rose', 'age' => 24, 'location' => 'New York', 'rating' => 4.9, 'views' => 1245, 'image' => 'https://images.unsplash.com/photo-1494790108777-467efef4493f?w=400&h=500&fit=crop'],
    ['name' => 'Isabella Marie', 'age' => 26, 'location' => 'Los Angeles', 'rating' => 4.8, 'views' => 987, 'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&h=500&fit=crop'],
    ['name' => 'Mia Johnson', 'age' => 23, 'location' => 'Miami', 'rating' => 4.9, 'views' => 1567, 'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=500&fit=crop'],
    ['name' => 'Emma Wilson', 'age' => 25, 'location' => 'Chicago', 'rating' => 4.7, 'views' => 876, 'image' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&h=500&fit=crop'],
    ['name' => 'Olivia Brown', 'age' => 27, 'location' => 'Houston', 'rating' => 4.9, 'views' => 2134, 'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&h=500&fit=crop'],
    ['name' => 'Ava Davis', 'age' => 22, 'location' => 'Phoenix', 'rating' => 4.6, 'views' => 654, 'image' => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=400&h=500&fit=crop'],
    ['name' => 'Sophia Miller', 'age' => 28, 'location' => 'Philadelphia', 'rating' => 4.8, 'views' => 1432, 'image' => 'https://images.unsplash.com/photo-1513094735237-8f2714d57c13?w=400&h=500&fit=crop'],
    ['name' => 'Isabella Garcia', 'age' => 24, 'location' => 'San Antonio', 'rating' => 4.7, 'views' => 1098, 'image' => 'https://images.unsplash.com/photo-1502323777036-f29e3972d82f?w=400&h=500&fit=crop'],
    ['name' => 'Mia Rodriguez', 'age' => 26, 'location' => 'San Diego', 'rating' => 4.9, 'views' => 1876, 'image' => 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=400&h=500&fit=crop'],
    ['name' => 'Emma Martinez', 'age' => 25, 'location' => 'Dallas', 'rating' => 4.8, 'views' => 1654, 'image' => 'https://images.unsplash.com/photo-1496440737103-cd596325d314?w=400&h=500&fit=crop'],
    ['name' => 'Olivia Anderson', 'age' => 27, 'location' => 'San Jose', 'rating' => 4.7, 'views' => 1321, 'image' => 'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?w=400&h=500&fit=crop'],
    ['name' => 'Ava Thomas', 'age' => 23, 'location' => 'Austin', 'rating' => 4.9, 'views' => 1987, 'image' => 'https://images.unsplash.com/photo-1464863979621-258859e62245?w=400&h=500&fit=crop'],
];
@endphp

@extends('layouts.frontend')

@section('content')
<section class="bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950 min-h-screen pb-16 overflow-x-hidden">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-4 md:gap-8 pt-4 md:pt-8 px-3 sm:px-4 md:px-8">
        <!-- Sidebar - Collapsible on Mobile -->
        <aside class="w-full md:w-64 bg-gradient-to-b from-purple-700 to-purple-500 rounded-2xl p-4 md:p-6 text-white shadow-xl md:sticky md:top-24 md:self-start relative overflow-hidden">
            <div class="flex items-center justify-between md:block">
                <h2 class="text-xl font-bold mb-3 md:mb-6 tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                    Categories
                </h2>
                <!-- Mobile toggle button -->
                <button class="md:hidden text-white p-2" onclick="this.parentElement.parentElement.classList.toggle('pb-4')">
                    <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <!-- Categories List - Collapsible on Mobile -->
            <div class="space-y-1 text-sm md:text-base max-h-[300px] md:max-h-none overflow-y-auto md:overflow-visible scrollbar-thin scrollbar-thumb-purple-400 scrollbar-track-transparent">
                <a href="#" class="font-bold bg-purple-400/80 text-white px-3 md:px-4 py-2 rounded flex justify-between items-center hover:bg-purple-500 transition mb-2">
                    All Live Cams
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @foreach($categories as $category)
                    <a href="#" class="hover:text-yellow-300 transition flex justify-between items-center px-3 md:px-4 py-1.5 md:py-2 rounded-lg hover:bg-purple-600/30">
                        {{ $category->name }}
                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @endforeach
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 min-w-0" x-data="{
            gridCols: 2,
            mobileMenuOpen: false,
            searchQuery: '',
            featuredCurrentSlide: 0,
            latestCurrentSlide: 0,
            autoplayInterval: null,
            touchStartX: 0,
            touchEndX: 0,
            featuredTotalSlides: {{ count($featuredEscorts) }},
            latestTotalSlides: {{ count($latestEscorts) }},

            init() {
                this.updateGridCols();
                window.addEventListener('resize', () => this.updateGridCols());
                this.startAutoplay();
            },

            updateGridCols() {
                if(window.innerWidth < 640) {
                    this.gridCols = 1;
                } else if(window.innerWidth < 768) {
                    this.gridCols = 2;
                } else if(window.innerWidth < 1024) {
                    this.gridCols = 3;
                } else {
                    this.gridCols = 4;
                }
            },

            // Featured slider functions
            featuredNextSlide() {
                this.featuredCurrentSlide = (this.featuredCurrentSlide + 1) % this.featuredTotalSlides;
            },

            featuredPrevSlide() {
                this.featuredCurrentSlide = (this.featuredCurrentSlide - 1 + this.featuredTotalSlides) % this.featuredTotalSlides;
            },

            featuredGoToSlide(index) {
                this.featuredCurrentSlide = index;
            },

            // Latest slider functions
            latestNextSlide() {
                this.latestCurrentSlide = (this.latestCurrentSlide + 1) % this.latestTotalSlides;
            },

            latestPrevSlide() {
                this.latestCurrentSlide = (this.latestCurrentSlide - 1 + this.latestTotalSlides) % this.latestTotalSlides;
            },

            latestGoToSlide(index) {
                this.latestCurrentSlide = index;
            },

            startAutoplay() {
                this.autoplayInterval = setInterval(() => {
                    this.featuredNextSlide();
                }, 5000);
            },

            stopAutoplay() {
                clearInterval(this.autoplayInterval);
            },

            // Touch events for mobile swipe on featured slider
            handleFeaturedTouchStart(event) {
                this.touchStartX = event.touches[0].clientX;
            },

            handleFeaturedTouchEnd(event) {
                this.touchEndX = event.changedTouches[0].clientX;
                this.handleFeaturedSwipe();
            },

            handleFeaturedSwipe() {
                const swipeThreshold = 50;
                const difference = this.touchStartX - this.touchEndX;

                if (Math.abs(difference) > swipeThreshold) {
                    if (difference > 0) {
                        this.featuredNextSlide();
                    } else {
                        this.featuredPrevSlide();
                    }
                }
            },

            // Touch events for mobile swipe on latest slider
            handleLatestTouchStart(event) {
                this.touchStartX = event.touches[0].clientX;
            },

            handleLatestTouchEnd(event) {
                this.touchEndX = event.changedTouches[0].clientX;
                this.handleLatestSwipe();
            },

            handleLatestSwipe() {
                const swipeThreshold = 50;
                const difference = this.touchStartX - this.touchEndX;

                if (Math.abs(difference) > swipeThreshold) {
                    if (difference > 0) {
                        this.latestNextSlide();
                    } else {
                        this.latestPrevSlide();
                    }
                }
            }
        }">

            <!-- Featured Models Slider (Original) -->
            <div class="mb-8 md:mb-10">
                <div class="flex flex-col gap-1 md:gap-2 mb-4">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white mb-1 md:mb-2 tracking-tight leading-tight">
                        Featured Models
                    </h1>
                    <p class="text-sm sm:text-base md:text-lg text-gray-400 max-w-2xl">
                        Discover our most popular live models right now
                    </p>
                </div>

                <!-- Featured Slider with Touch Support -->
                <div class="relative rounded-xl md:rounded-2xl overflow-hidden bg-gray-900/50 border border-gray-800"
                     @mouseenter="stopAutoplay()"
                     @mouseleave="startAutoplay()"
                     @touchstart="handleFeaturedTouchStart($event)"
                     @touchend="handleFeaturedTouchEnd($event)">

                    <!-- Slider Container -->
                    <div class="relative overflow-hidden">
                        <div class="flex transition-transform duration-500 ease-out"
                             :style="{ transform: 'translateX(-' + (featuredCurrentSlide * 100) + '%)' }">
                            @foreach($featuredEscorts as $index => $featured)
                            <div class="w-full flex-shrink-0">
                                <div class="relative h-[280px] xs:h-[320px] sm:h-[350px] md:h-[400px] lg:h-[450px]">
                                    <!-- Background Image with Overlay -->
                                    <img src="{{ $featured['image'] }}"
                                         alt="{{ $featured['name'] }}"
                                         class="w-full h-full object-cover object-center"
                                         loading="lazy">

                                    <!-- Gradient Overlays -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/70 to-transparent"></div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent"></div>
                                    <div class="absolute inset-0 md:hidden bg-gradient-to-t from-gray-950 via-gray-950/50 to-transparent"></div>

                                    <!-- Content -->
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="p-3 xs:p-4 sm:p-6 md:p-8 lg:p-10 max-w-2xl w-full">
                                            <!-- Featured Badge -->
                                            <div class="flex flex-wrap items-center gap-1 xs:gap-2 mb-1 xs:mb-2 md:mb-4">
                                                <span class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-black text-[10px] xs:text-xs sm:text-sm px-1.5 xs:px-2 sm:px-3 py-0.5 xs:py-1 rounded-full font-bold shadow-lg">
                                                    ⭐ FEATURED
                                                </span>
                                                @if($featured['online'])
                                                <span class="bg-green-500/20 text-green-400 text-[10px] xs:text-xs sm:text-sm px-1.5 xs:px-2 sm:px-3 py-0.5 xs:py-1 rounded-full border border-green-500/30 flex items-center gap-0.5 xs:gap-1">
                                                    <span class="w-1 h-1 xs:w-1.5 xs:h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                                    <span class="hidden xs:inline">LIVE</span>
                                                    <span class="xs:hidden">●</span>
                                                </span>
                                                @endif
                                            </div>

                                            <!-- Name and Age -->
                                            <div class="flex items-center flex-wrap gap-0.5 xs:gap-1 sm:gap-2 mb-0.5 xs:mb-1">
                                                <h2 class="text-base xs:text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-bold text-white truncate max-w-[120px] xs:max-w-[150px] sm:max-w-[200px] md:max-w-[250px] lg:max-w-[300px]">
                                                    {{ $featured['name'] }}
                                                </h2>
                                                <span class="text-sm xs:text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl text-pink-400 font-light">
                                                    {{ $featured['age'] }}
                                                </span>
                                                <span class="text-base xs:text-lg sm:text-xl">{{ $featured['country'] }}</span>
                                            </div>

                                            <!-- Location -->
                                            <p class="text-gray-300 text-[10px] xs:text-xs sm:text-sm md:text-base lg:text-lg mb-1 xs:mb-2 md:mb-4 flex items-center gap-0.5 xs:gap-1">
                                                <svg class="w-2.5 h-2.5 xs:w-3 xs:h-3 sm:w-4 sm:h-4 text-pink-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="truncate">{{ $featured['location'] }}</span>
                                            </p>

                                            <!-- Tags -->
                                            <div class="flex flex-wrap gap-0.5 xs:gap-1 sm:gap-2 mb-1 xs:mb-2 md:mb-4 overflow-x-auto pb-0.5 xs:pb-1 md:pb-0 -mx-0.5 xs:-mx-1 px-0.5 xs:px-1 md:overflow-visible md:mx-0 md:px-0">
                                                @foreach($featured['tags'] as $tag)
                                                <span class="bg-purple-600/80 text-white text-[8px] xs:text-[10px] sm:text-xs px-1 xs:px-1.5 sm:px-2 md:px-3 py-0.25 xs:py-0.5 sm:py-1 rounded-full border border-purple-400/30 backdrop-blur-sm whitespace-nowrap">
                                                    {{ $tag }}
                                                </span>
                                                @endforeach
                                            </div>

                                            <!-- Rating and Views -->
                                            <div class="flex items-center gap-1 xs:gap-2 sm:gap-4 mb-2 xs:mb-3 md:mb-6">
                                                <div class="flex items-center gap-0.5 xs:gap-1">
                                                    <div class="flex">
                                                        @for($i = 0; $i < 5; $i++)
                                                            <svg class="w-2 h-2 xs:w-2.5 xs:h-2.5 sm:w-3 sm:h-3 md:w-4 md:h-4 {{ $i < floor($featured['rating']) ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                    <span class="text-white font-bold text-[8px] xs:text-[10px] sm:text-xs md:text-sm ml-0.5 xs:ml-1">{{ $featured['rating'] }}</span>
                                                </div>
                                                <div class="flex items-center gap-0.5 xs:gap-1 text-gray-400 text-[8px] xs:text-[10px] sm:text-xs md:text-sm">
                                                    <svg class="w-2 h-2 xs:w-2.5 xs:h-2.5 sm:w-3 sm:h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    <span class="hidden xs:inline">{{ number_format($featured['views']) }} views</span>
                                                    <span class="xs:hidden">{{ floor($featured['views']/1000) }}.{{ floor(($featured['views']%1000)/100) }}k</span>
                                                </div>
                                            </div>

                                            <!-- CTA Buttons -->
                                            <div class="flex flex-col xs:flex-row gap-1 xs:gap-2 sm:gap-3">
                                                <button class="bg-gradient-to-r from-pink-500 to-purple-600 hover:from-purple-600 hover:to-pink-500 text-white px-2 xs:px-3 sm:px-4 md:px-6 lg:px-8 py-1 xs:py-1.5 sm:py-2 md:py-2.5 lg:py-3 rounded-lg font-bold text-[8px] xs:text-[10px] sm:text-xs md:text-sm lg:text-base shadow-xl transition-all transform hover:scale-105 flex items-center justify-center gap-0.5 xs:gap-1 sm:gap-2">
                                                    <svg class="w-2.5 h-2.5 xs:w-3 xs:h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="hidden xs:inline">Watch Live</span>
                                                    <span class="xs:hidden">Live</span>
                                                </button>
                                                <button class="bg-gray-800/80 hover:bg-gray-700 text-white px-2 xs:px-3 sm:px-4 md:px-6 lg:px-8 py-1 xs:py-1.5 sm:py-2 md:py-2.5 lg:py-3 rounded-lg font-bold text-[8px] xs:text-[10px] sm:text-xs md:text-sm lg:text-base border border-gray-700 transition-all backdrop-blur-sm flex items-center justify-center gap-0.5 xs:gap-1 sm:gap-2">
                                                    <svg class="w-2.5 h-2.5 xs:w-3 xs:h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                    </svg>
                                                    <span class="hidden xs:inline">Favorite</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Navigation Arrows -->
                    <button @click="featuredPrevSlide"
                            class="hidden sm:flex absolute left-2 md:left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-1.5 xs:p-2 md:p-3 transition-all backdrop-blur-sm">
                        <svg class="w-3 h-3 xs:w-4 xs:h-4 md:w-5 md:h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="featuredNextSlide"
                            class="hidden sm:flex absolute right-2 md:right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-1.5 xs:p-2 md:p-3 transition-all backdrop-blur-sm">
                        <svg class="w-3 h-3 xs:w-4 xs:h-4 md:w-5 md:h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <!-- Slide Indicators -->
                    <div class="absolute bottom-1 xs:bottom-2 sm:bottom-3 md:bottom-4 left-1/2 -translate-x-1/2 flex gap-1 xs:gap-1.5 sm:gap-2 md:gap-2">
                        @foreach($featuredEscorts as $index => $featured)
                        <button @click="featuredGoToSlide({{ $index }})"
                                class="transition-all duration-300 touch-manipulation"
                                :class="featuredCurrentSlide === {{ $index }} ? 'w-3 xs:w-4 sm:w-5 md:w-6 lg:w-8 h-1 xs:h-1.5 sm:h-2 bg-pink-500 rounded-full' : 'w-1 xs:w-1.5 sm:w-2 h-1 xs:h-1.5 sm:h-2 bg-white/50 hover:bg-white/80 rounded-full'">
                        </button>
                        @endforeach
                    </div>

                    <!-- Slide Counter -->
                    <div class="absolute top-1 xs:top-2 right-1 xs:right-2 sm:hidden bg-black/50 backdrop-blur-sm text-white text-[8px] xs:text-xs px-1.5 xs:px-2 py-0.5 xs:py-1 rounded-full">
                        <span x-text="featuredCurrentSlide + 1"></span>/{{ count($featuredEscorts) }}
                    </div>
                </div>
            </div>

            <!-- Latest Escorts from Australia Slider (New - Matching the image design) -->
            <div class="mb-8 md:mb-10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                        Latest Escorts from <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-pink-500">Australia</span>
                    </h2>
                    <a href="#" class="text-sm text-pink-400 hover:text-pink-300 flex items-center gap-1">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Latest Escorts Slider - Card Style -->
                <div class="relative"
                     @touchstart="handleLatestTouchStart($event)"
                     @touchend="handleLatestTouchEnd($event)">

                    <!-- Slider Container -->
                    <div class="relative overflow-hidden">
                        <div class="flex transition-transform duration-500 ease-out gap-3 md:gap-4"
                             :style="{ transform: 'translateX(-' + (latestCurrentSlide * (100 / (window.innerWidth < 640 ? 1 : window.innerWidth < 768 ? 2 : window.innerWidth < 1024 ? 3 : 4))) + '%)' }">

                            @foreach($latestEscorts as $index => $escort)
                            <div class="w-full min-w-[calc(100%-0.75rem)] xs:min-w-[calc(50%-0.375rem)] sm:min-w-[calc(33.333%-0.5rem)] lg:min-w-[calc(25%-0.5rem)] flex-shrink-0">
                                <div class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-xl overflow-hidden border border-gray-700 hover:border-pink-500 transition-all duration-300 group">
                                    <!-- Image Container -->
                                    <div class="relative aspect-square overflow-hidden">
                                        <img src="{{ $escort['image'] }}"
                                             alt="{{ $escort['name'] }}"
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">

                                        <!-- Time Badge -->
                                        <div class="absolute top-2 left-2 bg-black/70 backdrop-blur-sm text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                            {{ $escort['time_ago'] }}
                                        </div>

                                        <!-- Verification Badge -->
                                        @if($escort['verified'])
                                        <div class="absolute top-2 right-2 bg-blue-500/90 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Photos Verified</span>
                                        </div>
                                        @endif

                                        <!-- Featured Badge -->
                                        @if($escort['featured'])
                                        <div class="absolute bottom-2 left-2 bg-gradient-to-r from-yellow-500 to-yellow-600 text-black text-xs font-bold px-2 py-1 rounded">
                                            FEATURED
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="p-3">
                                        <!-- Name and Price -->
                                        <div class="flex items-center justify-between mb-1">
                                            <h3 class="text-white font-semibold text-sm sm:text-base truncate max-w-[120px]">
                                                {{ $escort['name'] }}
                                            </h3>
                                            <span class="text-pink-400 font-bold text-sm">From${{ $escort['price'] }}</span>
                                        </div>

                                        <!-- Location and Type -->
                                        <div class="flex items-center justify-between text-xs text-gray-400">
                                            <span class="truncate max-w-[100px]">{{ $escort['location'] }}</span>
                                            <span>{{ $escort['type'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Navigation Arrows - Hidden on mobile -->
                    <button @click="latestPrevSlide"
                            class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 bg-gray-800 hover:bg-gray-700 text-white rounded-full p-2 shadow-lg transition-all z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="latestNextSlide"
                            class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 bg-gray-800 hover:bg-gray-700 text-white rounded-full p-2 shadow-lg transition-all z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <!-- Slide Indicators for mobile -->
                    <div class="flex md:hidden justify-center gap-1.5 mt-4">
                        @for($i = 0; $i < ceil(count($latestEscorts) / (window.innerWidth < 640 ? 1 : 2)); $i++)
                        <button @click="latestGoToSlide({{ $i }})"
                                class="transition-all duration-300 touch-manipulation"
                                :class="latestCurrentSlide === {{ $i }} ? 'w-4 h-1.5 bg-pink-500 rounded-full' : 'w-1.5 h-1.5 bg-white/50 hover:bg-white/80 rounded-full'">
                        </button>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Unified Search & Filter Bar -->
            <div class="bg-gray-900/90 rounded-xl md:rounded-2xl p-4 md:p-6 shadow-lg border border-gray-800 mb-6">
                <!-- Gender Tabs - Horizontal Scroll on Mobile -->
                @php $activeGender = request('gender', 'female'); @endphp
                <div class="overflow-x-auto pb-2 -mx-1 px-1 scrollbar-thin scrollbar-thumb-purple-400 mb-4">
                    <div class="flex gap-2 min-w-max">
                        @foreach(gender_tabs() as $tab)
                            <a href="?gender={{ $tab->slug }}"
                               class="flex-shrink-0 px-5 sm:px-8 py-2.5 sm:py-3 rounded-lg font-bold text-xs sm:text-sm tracking-widest transition-all shadow-lg border-2 {{ $activeGender === $tab->slug ? 'bg-gray-800 text-purple-100 border-purple-400' : 'bg-gray-700 text-purple-200 hover:bg-purple-600 border-transparent' }}">
                                {{ strtoupper($tab->label) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Search Bar and Grid Controls -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 flex gap-2">
                        <input type="text"
                               placeholder="Search models or categories..."
                               x-model="searchQuery"
                               class="flex-1 px-4 py-2.5 sm:px-6 sm:py-3 rounded-lg bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 placeholder:text-gray-400 text-sm sm:text-base" />
                        <button class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-5 sm:px-7 py-2.5 sm:py-3 rounded-lg font-bold shadow hover:from-pink-500 hover:to-purple-600 transition text-sm sm:text-base whitespace-nowrap">
                            Search
                        </button>
                    </div>

                    <!-- Grid Switcher -->
                    <div class="flex gap-2 items-center justify-end sm:justify-start">
                        <span class="text-gray-400 text-sm hidden sm:block">View:</span>
                        <div class="flex gap-1 bg-gray-800 rounded-lg p-1">
                            <button @click="gridCols = 1" :class="gridCols === 1 ? 'bg-purple-500' : 'bg-gray-700 hover:bg-gray-600'"
                                    class="p-2 rounded-lg transition shadow sm:hidden">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="4" y="4" width="16" height="16" stroke-width="2"/>
                                </svg>
                            </button>
                            <button @click="gridCols = 2" :class="gridCols === 2 ? 'bg-purple-500' : 'bg-gray-700 hover:bg-gray-600'"
                                    class="p-2 rounded-lg transition shadow hidden sm:block">
                                <svg class="w-5 h-5 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/>
                                    <rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/>
                                </svg>
                            </button>
                            <button @click="gridCols = 3" :class="gridCols === 3 ? 'bg-purple-500' : 'bg-gray-700 hover:bg-gray-600'"
                                    class="p-2 rounded-lg transition shadow hidden sm:block">
                                <svg class="w-5 h-5 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="2" y="2" width="6" height="6"/><rect x="9" y="2" width="6" height="6"/><rect x="16" y="2" width="6" height="6"/>
                                    <rect x="2" y="9" width="6" height="6"/><rect x="9" y="9" width="6" height="6"/><rect x="16" y="9" width="6" height="6"/>
                                </svg>
                            </button>
                            <button @click="gridCols = 4" :class="gridCols === 4 ? 'bg-purple-500' : 'bg-gray-700 hover:bg-gray-600'"
                                    class="p-2 rounded-lg transition shadow hidden lg:block">
                                <svg class="w-5 h-5 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="4" height="4"/><rect x="8" y="3" width="4" height="4"/>
                                    <rect x="13" y="3" width="4" height="4"/><rect x="18" y="3" width="4" height="4"/>
                                    <rect x="3" y="8" width="4" height="4"/><rect x="8" y="8" width="4" height="4"/>
                                </svg>
                            </button>
                            <button @click="gridCols = 5" :class="gridCols === 5 ? 'bg-purple-500' : 'bg-gray-700 hover:bg-gray-600'"
                                    class="p-2 rounded-lg transition shadow hidden xl:block">
                                <svg class="w-5 h-5 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="2" y="2" width="3" height="3"/><rect x="6" y="2" width="3" height="3"/><rect x="10" y="2" width="3" height="3"/><rect x="14" y="2" width="3" height="3"/><rect x="18" y="2" width="3" height="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Count -->
            <div class="flex justify-between items-center mb-4 px-1">
                <p class="text-sm text-gray-400">
                    Showing <span class="text-white font-semibold">36</span> models
                </p>
                <select class="bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-purple-500">
                    <option>Sort by: Popular</option>
                    <option>Sort by: Newest</option>
                    <option>Sort by: Online</option>
                </select>
            </div>

            <!-- Model Grid - Fully Responsive -->
            <div class="grid gap-3 sm:gap-4 md:gap-6 lg:gap-8"
                :class="{
                    'grid-cols-1': gridCols === 1,
                    'grid-cols-2': gridCols === 2,
                    'grid-cols-2 sm:grid-cols-3': gridCols === 3,
                    'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4': gridCols === 4,
                    'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5': gridCols === 5
                }">
                @for ($i = 0; $i < 36; $i++)
                <div class="relative group rounded-xl md:rounded-2xl overflow-hidden shadow-xl bg-gradient-to-br from-gray-900 to-gray-800 hover:scale-[1.02] md:hover:scale-105 transition-all duration-300 border-2 border-transparent hover:border-purple-500">
                    <!-- Image Container with Aspect Ratio -->
                    <div class="relative aspect-[3/4] overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/women/{{ $i % 100 }}.jpg"
                             alt="Model"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                             loading="lazy">

                        <!-- Overlay Gradient -->
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent opacity-60"></div>

                        <!-- Badges -->
                        <div class="absolute top-2 left-2 flex gap-1">
                            <span class="bg-gradient-to-r from-pink-500 to-purple-600 text-white text-xs px-2 py-1 rounded font-bold shadow-lg animate-pulse">
                                LIVE
                            </span>
                            <span class="bg-black/70 text-white text-xs px-2 py-1 rounded font-medium backdrop-blur-sm">
                                18+
                            </span>
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="absolute top-2 right-2 flex gap-1 opacity-0 md:group-hover:opacity-100 transition-opacity">
                            <button class="bg-pink-500 hover:bg-pink-600 text-white rounded-full p-1.5 md:p-2 shadow-lg transform hover:scale-110 transition">
                                <svg class="w-3 h-3 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Model Info -->
                    <div class="p-2 md:p-3">
                        <div class="flex items-center justify-between gap-1">
                            <span class="font-semibold text-white text-sm md:text-base truncate">ModelName{{ $i+1 }}</span>
                            <span class="text-pink-400 text-xs whitespace-nowrap">({{ rand(18,35) }})</span>
                        </div>

                        <!-- Category and Tags -->
                        <div class="text-xs text-gray-400 mt-0.5 md:mt-1 truncate">Category • Online now</div>

                        <div class="flex items-center gap-1 mt-1 md:mt-2 flex-wrap">
                            <span class="bg-purple-600 text-white text-[10px] md:text-xs px-1.5 md:px-2 py-0.5 rounded">HD</span>
                            <span class="bg-gray-900 text-pink-400 text-[10px] md:text-xs px-1.5 md:px-2 py-0.5 rounded border border-pink-500/30">VIP</span>
                            <span class="text-green-400 text-[10px] ml-auto flex items-center gap-0.5">
                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                Online
                            </span>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            <!-- Mobile Quick Actions - Fixed Bottom Bar -->
            <div class="fixed bottom-0 left-0 right-0 bg-gray-900/95 backdrop-blur-lg border-t border-gray-800 p-3 md:hidden z-50">
                <div class="flex justify-around items-center max-w-md mx-auto">
                    <button class="flex flex-col items-center text-pink-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span class="text-xs">Filters</span>
                    </button>
                    <button class="flex flex-col items-center text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="text-xs">Sort</span>
                    </button>
                    <button class="flex flex-col items-center text-white bg-gradient-to-r from-purple-600 to-pink-500 rounded-full px-6 py-2 -mt-8 shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span class="text-xs">Search</span>
                    </button>
                    <button class="flex flex-col items-center text-yellow-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        <span class="text-xs">Favorites</span>
                    </button>
                </div>
            </div>

            <!-- Load More Button -->
            <div class="flex justify-center mt-8 md:mt-10 mb-4 md:mb-0">
                <button class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-pink-500 hover:from-pink-500 hover:to-purple-600 text-white px-6 sm:px-10 py-3 md:py-4 rounded-xl font-bold text-sm sm:text-base md:text-lg shadow-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                    <span>SHOW MORE MODELS</span>
                    <svg class="w-4 h-4 md:w-5 md:h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7m14-6l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Add custom scrollbar and mobile optimization styles -->
<style>
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
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #c084fc;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .group:hover {
        transform: none !important;
    }
    .group:hover .opacity-0 {
        opacity: 1 !important;
    }
}

/* Extra small devices (under 380px) */
@media (max-width: 380px) {
    .xs\:hidden {
        display: none;
    }
    .xs\:inline {
        display: inline;
    }
    .xs\:flex-row {
        flex-direction: row;
    }
}

/* Very small devices (under 320px) */
@media (max-width: 320px) {
    .group .text-xs {
        font-size: 0.65rem;
    }
}

/* Smooth transitions and touch optimizations */
* {
    -webkit-tap-highlight-color: transparent;
}

/* Improve touch targets on mobile */
button, a {
    touch-action: manipulation;
}

/* Prevent text overflow on very small screens */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Custom breakpoint for extra small devices */
@media (min-width: 380px) {
    .xs\:block {
        display: block;
    }
    .xs\:inline {
        display: inline;
    }
    .xs\:hidden {
        display: none;
    }
}
</style>
@endsection
