@extends('layouts.frontend')

@section('content')
@php
    $plans = [
        [
            'name' => 'Regular Profile',
            'subtitle' => 'Great for starting visibility',
            'accent' => 'gray',
            'preselect_credits' => 30,
            'prices' => [
                ['label' => '1 week', 'amount' => '$15.99'],
                ['label' => '1 month', 'amount' => '$47.99'],
            ],
            'features' => [
                'Visible in standard listing feed',
                'Appears in city and category search',
                'Edit profile details anytime',
                'Basic support and moderation',
            ],
        ],
        [
            'name' => 'VIP Profile',
            'subtitle' => 'More calls and more profile views',
            'accent' => 'pink',
            'preselect_credits' => 60,
            'prices' => [
                ['label' => '3 days', 'amount' => '$14.99'],
                ['label' => '1 week', 'amount' => '$29.99'],
                ['label' => '1 month', 'amount' => '$99.99'],
            ],
            'features' => [
                'Priority position in listing blocks',
                'Highlighted style for stronger visibility',
                'More exposure on homepage sections',
                'Increased profile engagement potential',
            ],
            'recommended' => true,
        ],
        [
            'name' => 'Diamond Profile',
            'subtitle' => 'Maximum placement and branding',
            'accent' => 'dark',
            'preselect_credits' => 120,
            'prices' => [
                ['label' => '3 days', 'amount' => '$22.99'],
                ['label' => '1 week', 'amount' => '$42.99'],
                ['label' => '1 month', 'amount' => '$129.99'],
            ],
            'features' => [
                'Top-priority display in premium zones',
                'Diamond badge on supported cards',
                'Extended time in boosted positions',
                'Best reach for competitive cities',
            ],
        ],
    ];

    $relatedListings = [
        ['name' => 'Avery Rose', 'age' => 24, 'city' => 'Sydney', 'price' => '$600', 'image' => 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=500&auto=format&fit=crop'],
        ['name' => 'Mira', 'age' => 26, 'city' => 'Melbourne', 'price' => '$500', 'image' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=500&auto=format&fit=crop'],
        ['name' => 'Sabrina', 'age' => 29, 'city' => 'Brisbane', 'price' => '$700', 'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=500&auto=format&fit=crop'],
        ['name' => 'Luna', 'age' => 23, 'city' => 'Perth', 'price' => '$550', 'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop'],
        ['name' => 'Kiara', 'age' => 27, 'city' => 'Gold Coast', 'price' => '$650', 'image' => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=500&auto=format&fit=crop'],
        ['name' => 'Bella', 'age' => 25, 'city' => 'Adelaide', 'price' => '$580', 'image' => 'https://images.unsplash.com/photo-1525134479668-1bee5c7c6845?w=500&auto=format&fit=crop'],
    ];
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-7xl">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Choose Your Membership</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-gray-600 sm:text-base">
                Activate your profile and increase visibility with a plan that matches your promotion goals.
            </p>
        </div>

        <div class="mb-10 grid grid-cols-1 gap-5 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $headerClasses = match ($plan['accent']) {
                        'pink' => 'bg-[#e04ecb] text-white',
                        'dark' => 'bg-gray-900 text-white',
                        default => 'bg-white text-gray-900',
                    };

                    $buttonClasses = match ($plan['accent']) {
                        'pink' => 'bg-white/20 text-white hover:bg-white/30 border border-white/40',
                        'dark' => 'bg-white/10 text-white hover:bg-white/20 border border-white/30',
                        default => 'bg-[#e04ecb] text-white hover:bg-[#c13ab0]',
                    };

                    $cardBorder = !empty($plan['recommended']) ? 'border-pink-300 ring-2 ring-pink-100' : 'border-gray-200';
                @endphp

                <div class="overflow-hidden rounded-2xl border {{ $cardBorder }} bg-white shadow-sm">
                    <div class="px-5 py-4 {{ $headerClasses }}">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-xl font-bold">{{ $plan['name'] }}</h2>
                            @if(!empty($plan['recommended']))
                                <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-[#e04ecb]">Most Popular</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm {{ $plan['accent'] === 'gray' ? 'text-gray-600' : 'text-white/90' }}">{{ $plan['subtitle'] }}</p>
                    </div>

                    <div class="grid grid-cols-{{ count($plan['prices']) }} gap-2 border-b border-gray-100 bg-gray-50 p-4">
                        @foreach($plan['prices'] as $price)
                            <div class="rounded-lg border border-gray-200 bg-white p-3 text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ $price['amount'] }}</p>
                                <p class="text-xs font-medium text-gray-500">{{ $price['label'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 p-5">
                        @foreach($plan['features'] as $feature)
                            <div class="flex items-start gap-2 text-sm text-gray-700">
                                <i class="fa-solid fa-circle-check mt-0.5 text-[#e04ecb]"></i>
                                <span>{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-5 pb-5">
                        <a href="{{ url('/purchase-credit') }}?credits={{ $plan['preselect_credits'] }}" class="block w-full rounded-lg px-4 py-2 text-center text-sm font-semibold transition {{ $buttonClasses }}">Choose Plan</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-10 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-3xl font-bold text-[#e04ecb]">Sourabh Wadhwa</h2>
                    <p class="text-sm text-gray-500">Melbourne</p>
                </div>
                <a href="{{ url('/after-image-upload') }}" class="text-sm font-medium text-[#e04ecb] hover:text-[#c13ab0] hover:underline">Back to dashboard</a>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=900&auto=format&fit=crop" alt="Profile image" class="h-[420px] w-full rounded-xl object-cover">
                    <div class="mt-4">
                        <h3 class="text-lg font-semibold text-gray-900">About me</h3>
                        <p class="mt-2 text-sm text-gray-600">Friendly and genuine companion. Private outcall and in-call options available. Message first for bookings and availability.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <h4 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-700">Contact</h4>
                        <button class="mb-3 w-full rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Send WhatsApp Message</button>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="text-xl font-bold text-gray-900">0415 573 077</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-4">
                        <h4 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-700">My profile</h4>
                        <div class="grid grid-cols-2 gap-y-2 text-sm">
                            <span class="text-gray-500">Nationality</span><span class="text-gray-800">-</span>
                            <span class="text-gray-500">Ethnicity</span><span class="text-gray-800">-</span>
                            <span class="text-gray-500">Hair color</span><span class="text-gray-800">Brown</span>
                            <span class="text-gray-500">Body type</span><span class="text-gray-800">Slim</span>
                            <span class="text-gray-500">Languages</span><span class="text-gray-800">English</span>
                            <span class="text-gray-500">Location</span><span class="text-gray-800">Melbourne</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-4">
                        <h4 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-700">Rates</h4>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="pb-2">Time</th>
                                    <th class="pb-2">Outcall</th>
                                    <th class="pb-2">In-call</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr><td class="py-1">30 min</td><td>-</td><td>-</td></tr>
                                <tr><td class="py-1">1 hour</td><td>$350</td><td>$300</td></tr>
                                <tr><td class="py-1">2 hours</td><td>$650</td><td>$600</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h3 class="mb-4 text-xl font-bold text-gray-900">Related Listings</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($relatedListings as $listing)
                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <img src="{{ $listing['image'] }}" alt="{{ $listing['name'] }}" class="h-40 w-full object-cover">
                        <div class="p-2.5">
                            <h4 class="truncate text-sm font-semibold text-gray-900">{{ $listing['name'] }}</h4>
                            <p class="text-xs text-gray-500">{{ $listing['age'] }} years • {{ $listing['city'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-[#e04ecb]">1 hour {{ $listing['price'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
