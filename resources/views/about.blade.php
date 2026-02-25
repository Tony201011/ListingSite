@php
// Prevent horizontal scroll on html/body at all zoom levels
echo '<style>html,body{overflow-x:hidden!important;}</style>';

// About Us content
$aboutContent = [
    'title' => 'About RealBabes',
    'subtitle' => 'Australia\'s Premier Escort Directory',
    'description' => 'Since 2018, RealBabes has been Australia\'s most trusted platform connecting verified babes with genuine clients. We pride ourselves on providing a safe, professional, and discreet environment for adult entertainment.',
    'mission' => 'Our mission is to empower independent escorts with the tools they need to succeed while providing clients with a seamless, secure way to find genuine companions.',
    'vision' => 'We envision a world where adult entertainment is respected, safe, and accessible for consenting adults, with technology bridging the gap between quality providers and discerning clients.'
];

// Team members
$teamMembers = [
    [
        'name' => 'Sarah Mitchell',
        'role' => 'Founder & CEO',
        'bio' => 'Former escort with 10+ years experience, Sarah founded RealBabes to create a safer platform for the industry.',
        'image' => 'https://randomuser.me/api/portraits/women/44.jpg',
    ],
    [
        'name' => 'Jessica Chen',
        'role' => 'Head of Verification',
        'bio' => 'Ensures every profile is 100% authentic with rigorous verification processes.',
        'image' => 'https://randomuser.me/api/portraits/women/63.jpg',
    ],
    [
        'name' => 'Emma Thompson',
        'role' => 'Community Manager',
        'bio' => 'Dedicated to maintaining a respectful and supportive community for all members.',
        'image' => 'https://randomuser.me/api/portraits/women/86.jpg',
    ],
    [
        'name' => 'Rachel Williams',
        'role' => 'Client Relations',
        'bio' => '24/7 support for both babes and clients, ensuring every experience is exceptional.',
        'image' => 'https://randomuser.me/api/portraits/women/90.jpg',
    ],
];

// Stats
$stats = [
    ['number' => '500+', 'label' => 'Verified Babes'],
    ['number' => '50K+', 'label' => 'Happy Clients'],
    ['number' => '24/7', 'label' => 'Support'],
    ['number' => '6+', 'label' => 'Years Online'],
];

// Values
$values = [
    [
        'title' => 'Safety First',
        'description' => 'Every profile is manually verified. Your safety is our top priority.',
        'icon' => '🛡️',
    ],
    [
        'title' => 'Privacy Protected',
        'description' => 'Your identity and interactions are completely confidential.',
        'icon' => '🔒',
    ],
    [
        'title' => 'Authenticity',
        'description' => '100% real babes. No fakes, no scams, no bots.',
        'icon' => '✓',
    ],
    [
        'title' => 'Fair Pricing',
        'description' => 'Transparent pricing with no hidden fees. From just $0.79/day.',
        'icon' => '💰',
    ],
];

// Testimonials
$testimonials = [
    [
        'name' => 'Sophia Rose',
        'role' => 'Premium Babe',
        'content' => 'RealBabes changed my life. The verification process ensures only serious clients, and the support team is amazing.',
        'rating' => 5,
        'image' => 'https://images.unsplash.com/photo-1494790108777-467efef4493f?w=200&h=200&fit=crop',
    ],
    [
        'name' => 'Michael Thompson',
        'role' => 'VIP Client',
        'content' => 'The most professional directory in Australia. Easy to use, great selection, and genuine profiles.',
        'rating' => 5,
        'image' => 'https://randomuser.me/api/portraits/men/32.jpg',
    ],
    [
        'name' => 'Isabella Marie',
        'role' => 'Top Babe',
        'content' => 'I\'ve tried other platforms, but RealBabes gives me the best exposure and the highest quality clients.',
        'rating' => 5,
        'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop',
    ],
];
@endphp

@extends('layouts.frontend')

@section('content')
@php
// Prevent horizontal scroll on html/body at all zoom levels
echo '<style>html,body{overflow-x:hidden!important;}</style>';

// About Us content
$aboutContent = [
    'title' => 'About RealBabes',
    'subtitle' => 'Australia\'s Premier Escort Directory',
    'description' => 'Since 2018, RealBabes has been Australia\'s most trusted platform connecting verified babes with genuine clients. We pride ourselves on providing a safe, professional, and discreet environment for adult entertainment.',
    'mission' => 'Our mission is to empower independent escorts with the tools they need to succeed while providing clients with a seamless, secure way to find genuine companions.',
    'vision' => 'We envision a world where adult entertainment is respected, safe, and accessible for consenting adults, with technology bridging the gap between quality providers and discerning clients.'
];

// Team members
$teamMembers = [
    [
        'name' => 'Sarah Mitchell',
        'role' => 'Founder & CEO',
        'bio' => 'Former escort with 10+ years experience, Sarah founded RealBabes to create a safer platform for the industry.',
        'image' => 'https://randomuser.me/api/portraits/women/44.jpg',
    ],
    [
        'name' => 'Jessica Chen',
        'role' => 'Head of Verification',
        'bio' => 'Ensures every profile is 100% authentic with rigorous verification processes.',
        'image' => 'https://randomuser.me/api/portraits/women/63.jpg',
    ],
    [
        'name' => 'Emma Thompson',
        'role' => 'Community Manager',
        'bio' => 'Dedicated to maintaining a respectful and supportive community for all members.',
        'image' => 'https://randomuser.me/api/portraits/women/86.jpg',
    ],
    [
        'name' => 'Rachel Williams',
        'role' => 'Client Relations',
        'bio' => '24/7 support for both babes and clients, ensuring every experience is exceptional.',
        'image' => 'https://randomuser.me/api/portraits/women/90.jpg',
    ],
];

// Stats
$stats = [
    ['number' => '500+', 'label' => 'Verified Babes'],
    ['number' => '50K+', 'label' => 'Happy Clients'],
    ['number' => '24/7', 'label' => 'Support'],
    ['number' => '6+', 'label' => 'Years Online'],
];

// Values
$values = [
    [
        'title' => 'Safety First',
        'description' => 'Every profile is manually verified. Your safety is our top priority.',
        'icon' => '🛡️',
    ],
    [
        'title' => 'Privacy Protected',
        'description' => 'Your identity and interactions are completely confidential.',
        'icon' => '🔒',
    ],
    [
        'title' => 'Authenticity',
        'description' => '100% real babes. No fakes, no scams, no bots.',
        'icon' => '✓',
    ],
    [
        'title' => 'Fair Pricing',
        'description' => 'Transparent pricing with no hidden fees. From just $0.79/day.',
        'icon' => '💰',
    ],
];

// Testimonials
$testimonials = [
    [
        'name' => 'Sophia Rose',
        'role' => 'Premium Babe',
        'content' => 'RealBabes changed my life. The verification process ensures only serious clients, and the support team is amazing.',
        'rating' => 5,
        'image' => 'https://images.unsplash.com/photo-1494790108777-467efef4493f?w=200&h=200&fit=crop',
    ],
    [
        'name' => 'Michael Thompson',
        'role' => 'VIP Client',
        'content' => 'The most professional directory in Australia. Easy to use, great selection, and genuine profiles.',
        'rating' => 5,
        'image' => 'https://randomuser.me/api/portraits/men/32.jpg',
    ],
    [
        'name' => 'Isabella Marie',
        'role' => 'Top Babe',
        'content' => 'I\'ve tried other platforms, but RealBabes gives me the best exposure and the highest quality clients.',
        'rating' => 5,
        'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop',
    ],
];
@endphp

@extends('layouts.frontend')

@section('content')
<section class="bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950 min-h-screen pb-16 overflow-x-hidden">
    <!-- Hero Banner -->
    <div class="relative h-[300px] md:h-[400px] overflow-hidden">
        <!-- Banner Image -->
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1600&auto=format&fit=crop"
                 alt="About Us Banner"
                 class="w-full h-full object-cover">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/80 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent"></div>
        </div>

        <!-- Banner Content -->
        <div class="relative max-w-7xl mx-auto px-3 sm:px-4 md:px-8 h-full flex items-center">
            <div class="text-white max-w-3xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold">
                            About <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500">RealBabes</span>
                        </h1>
                        <p class="text-xl text-gray-300 mt-2">Australia's Most Trusted Escort Directory Since 2018</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overlay on Banner -->
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-gray-950 via-gray-950/80 to-transparent pt-20 pb-8">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($stats as $stat)
                    <div class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-white">{{ $stat['number'] }}</div>
                        <div class="text-sm text-gray-400">{{ $stat['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto pt-16 md:pt-24 px-3 sm:px-4 md:px-8">
        <!-- Our Story Section -->
        <div class="grid md:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-6">
                    Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500">Story</span>
                </h2>
                <p class="text-gray-300 text-lg mb-6 leading-relaxed">
                    {{ $aboutContent['description'] }}
                </p>
                <p class="text-gray-400 mb-6 leading-relaxed">
                    {{ $aboutContent['mission'] }}
                </p>
                <p class="text-gray-400 leading-relaxed">
                    {{ $aboutContent['vision'] }}
                </p>

                <!-- Feature List -->
                <div class="grid grid-cols-2 gap-4 mt-8">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-300">100% Verified Profiles</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-300">24/7 Support</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-300">Privacy Guaranteed</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-300">Fair Pricing</span>
                    </div>
                </div>
            </div>

            <!-- Image Collage -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-4">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=500&fit=crop"
                         alt="Team Meeting"
                         class="rounded-2xl object-cover h-48 w-full">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&h=300&fit=crop"
                         alt="Office"
                         class="rounded-2xl object-cover h-32 w-full">
                </div>
                <div class="space-y-4 pt-8">
                    <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=400&h=300&fit=crop"
                         alt="Team Work"
                         class="rounded-2xl object-cover h-32 w-full">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&h=500&fit=crop"
                         alt="Collaboration"
                         class="rounded-2xl object-cover h-48 w-full">
                </div>
            </div>
        </div>

        <!-- Our Values -->
        <div class="mb-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                    Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500">Values</span>
                </h2>
                <p class="text-gray-400 max-w-2xl mx-auto">
                    What makes RealBabes the most trusted platform in Australia
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($values as $value)
                <div class="bg-gray-800/50 backdrop-blur-lg rounded-2xl p-6 border border-gray-700 hover:border-purple-500 transition-all group">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">{{ $value['icon'] }}</div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ $value['title'] }}</h3>
                    <p class="text-gray-400 text-sm">{{ $value['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Team Section -->
        <div class="mb-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                    Meet Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500">Team</span>
                </h2>
                <p class="text-gray-400 max-w-2xl mx-auto">
                    Dedicated professionals working 24/7 to ensure your experience is exceptional
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($teamMembers as $member)
                <div class="bg-gray-800/50 backdrop-blur-lg rounded-2xl overflow-hidden border border-gray-700 hover:border-purple-500 transition-all group">
                    <div class="relative h-64 overflow-hidden">
                        <img src="{{ $member['image'] }}"
                             alt="{{ $member['name'] }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-1">{{ $member['name'] }}</h3>
                        <p class="text-pink-400 text-sm mb-3">{{ $member['role'] }}</p>
                        <p class="text-gray-400 text-sm">{{ $member['bio'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Testimonials -->
        <div class="mb-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                    What Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500">Community Says</span>
                </h2>
                <p class="text-gray-400 max-w-2xl mx-auto">
                    Real feedback from real members of the RealBabes community
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($testimonials as $testimonial)
                <div class="bg-gray-800/50 backdrop-blur-lg rounded-2xl p-6 border border-gray-700">
                    <div class="flex items-center gap-4 mb-4">
                        <img src="{{ $testimonial['image'] }}"
                             alt="{{ $testimonial['name'] }}"
                             class="w-16 h-16 rounded-full object-cover border-2 border-purple-500">
                        <div>
                            <h4 class="font-bold text-white">{{ $testimonial['name'] }}</h4>
                            <p class="text-sm text-gray-400">{{ $testimonial['role'] }}</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-4">"{{ $testimonial['content'] }}"</p>
                    <div class="flex gap-1">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5 {{ $i < $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-purple-900/50 to-pink-900/50 rounded-3xl p-12 text-center border border-purple-500/30">
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                Ready to <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-pink-500">Join Us?</span>
            </h2>
            <p class="text-gray-300 max-w-2xl mx-auto mb-8">
                Whether you're a babe looking to advertise or a client seeking genuine companions, RealBabes is here for you.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/signup" class="bg-gradient-to-r from-pink-500 to-purple-600 hover:from-purple-600 hover:to-pink-500 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-xl transition-all transform hover:scale-105">
                    Create Your Account
                </a>
                <a href="/contact" class="bg-gray-800 hover:bg-gray-700 text-white px-8 py-4 rounded-xl font-bold text-lg border border-gray-700 transition-all transform hover:scale-105">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
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

/* Mobile optimizations */
@media (max-width: 640px) {
    .group:hover {
        transform: none !important;
    }
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.floating {
    animation: float 3s ease-in-out infinite;
}
</style>
@endsection
@endsection
