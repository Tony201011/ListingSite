@extends('layouts.frontend')
@push('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/profile-nav-mobile.css') }}">
@endpush

@section('title', $profile['name'] . ' Profile')

@php
$profileTags = !empty($profile['attributes']) ? $profile['attributes'] : [];
@endphp

@section('content')
<div class="min-h-screen overflow-x-hidden bg-gray-50 text-gray-800">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-700">Home</a>
            <span>›</span>
            <span>Listings</span>
            <span>›</span>
            <span class="text-gray-700">{{ $profile['name'] }}</span>
        </div>

        @php
            $primaryPhone = trim((string) ($profile['phone'] ?? $profile['whatsapp'] ?? ''));
            $phoneHref = preg_replace('/[^0-9+]/', '', $primaryPhone);
            $whatsAppHref = preg_replace('/[^0-9]/', '', $phoneHref);

            $priceList = collect($profile['price_list'] ?? [])->values();

            $availabilityList = collect($profile['availability_list'] ?? [])->values();

            $profileStats = $profileStats ?? [
                ['label' => 'Age group', 'value' => $profile['age_group'] ?? '—'],
                ['label' => 'Ethnicity', 'value' => $profile['ethnicity'] ?? '—'],
                ['label' => 'Hair color', 'value' => $profile['hair_color'] ?? '—'],
                ['label' => 'Hair length', 'value' => $profile['hair_length'] ?? '—'],
                ['label' => 'Body type', 'value' => $profile['body_type'] ?? '—'],
                ['label' => 'Bust size', 'value' => $profile['bust_size'] ?? '—'],
                ['label' => 'Length', 'value' => $profile['your_length'] ?? '—'],
            ];

            $galleryImages = !empty($profile['images']) ? $profile['images'] : (!empty($profile['image']) ? [$profile['image']] : []);

            $servicesProvided = !empty($profile['services_provided']) ? $profile['services_provided'] : [];

            $availableNow = $profile['available_now'] ?? false;
            $isOnline = $profile['active'] ?? false;
            $availableExpiresAt = $profile['available_expires_at'] ?? null;
            $availableTillText = $availableNow && $availableExpiresAt
                ? ' - AVAILABLE TILL ' . \Carbon\Carbon::parse($availableExpiresAt)->format('g:ia')
                : '';

            $profileUrl = route('profile.show', ['slug' => $profile['slug']]);
            $profileUrlDisplay = parse_url($profileUrl, PHP_URL_HOST) . '/profile/' . $profile['slug'];

            $introTagline = '';
            if (!empty($profile['age']) && !empty($profile['introduction_line'])) {
                $introTagline = $profile['age'] . ' - ' . $profile['introduction_line'];
            } elseif (!empty($profile['age'])) {
                $introTagline = (string) $profile['age'];
            } elseif (!empty($profile['introduction_line'])) {
                $introTagline = $profile['introduction_line'];
            }
        @endphp

        <div class="max-w-5xl mx-auto">
                <div class="text-center mb-8">
                    @if($availableNow)
                    <div class="inline-block mb-2 px-6 py-2 rounded bg-[#e13a8b] text-white font-extrabold text-base tracking-wide" style="letter-spacing:0.5px;">
                        AVAILABLE NOW{{ $availableTillText }}
                    </div>
                    @elseif($isOnline)
                    <div class="inline-block mb-2 px-6 py-2 rounded bg-green-500 text-white font-extrabold text-base tracking-wide" style="letter-spacing:0.5px;">
                        ONLINE NOW
                    </div>
                    @endif
                    <h1 class="text-4xl font-extrabold text-pink-600" style="color:#e13a8b;">
                        {{ $profile['name'] }}
                    </h1>
                    @if(!empty($profile['city']))
                        <div class="flex items-center justify-center mt-1">
                            <span class="text-base font-semibold text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-pink-400"></i>
                                {{ $profile['city'] }}
                            </span>
                        </div>
                    @endif
                    @if(!empty($introTagline))
                    <div class="mt-1 text-lg text-gray-700 font-medium">{{ $introTagline }}</div>
                    @endif
                </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">


                <!-- Gallery (left, spans 2 columns) -->
                <div class="md:col-span-2 flex flex-col gap-4 relative">
                    <!-- Previous Button (left corner) -->
                          <a href="{{ route('profile.show', ['slug' => $prevProfile['slug']]) }}"
                              class="md:fixed md:left-0 md:top-1/2 md:-translate-y-1/2 z-30 flex flex-col items-center group mobile-nav-btn-wrapper"
                              style="margin-left: 0.5rem;">
                        <div class="rounded-xl p-0.5 bg-white shadow-lg border border-pink-200">
                            <button class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded-xl flex flex-col items-center shadow-lg min-w-[100px] min-h-[60px] mobile-transparent-nav-btn">
                                <span class="flex items-center"><i class="fa-solid fa-arrow-left text-xl mr-2"></i> <span class="text-xs font-semibold">PREVIOUS</span></span>
                                <span class="text-base font-extrabold mt-0.5">{{ $prevProfile['name'] }}</span>
                            </button>
                        </div>
                    </a>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach(array_slice($galleryImages, 0, 2) as $img)
                            <img src="{{ $img }}" alt="{{ $profile['name'] }} image" class="rounded-xl w-full h-64 object-cover gallery-img-clickable cursor-pointer">
                        @endforeach
                    </div>
                        <!-- Next Button (right corner) -->
                                <a href="{{ route('profile.show', ['slug' => $nextProfile['slug']]) }}"
                                    class="md:fixed md:right-0 md:top-1/2 md:-translate-y-1/2 z-30 flex flex-col items-center group mobile-nav-btn-wrapper"
                                    style="margin-right: 0.5rem;">
                            <div class="rounded-xl p-0.5 bg-white shadow-lg border border-pink-200">
                                <button class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded-xl flex flex-col items-center shadow-lg min-w-[100px] min-h-[60px] mobile-transparent-nav-btn">
                                    <span class="flex items-center"><span class="text-xs font-semibold">NEXT</span> <i class="fa-solid fa-arrow-right text-xl ml-2"></i></span>
                                    <span class="text-base font-extrabold mt-0.5">{{ $nextProfile['name'] }}</span>
                                </button>
                            </div>
                        </a>
                <!-- Currently Touring Section -->
                @if(!empty($profile['tours']))
                <div class="mb-6">
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                        <div class="mb-6">
                            <div class="flex items-center mb-2">
                                <i class="fa-solid fa-location-dot text-pink-500 text-2xl mr-3"></i>
                                <span class="text-2xl font-extrabold text-pink-600">Currently touring in {{ $profile['tours'][0]['city'] }}</span>
                            </div>
                            <span class="font-bold text-lg text-gray-800">{{ $profile['tours'][0]['from'] }} - {{ $profile['tours'][0]['to'] }}</span>
                        </div>
                        <a href="#upcoming-tours" class="border border-pink-300 text-pink-400 px-6 py-3 rounded-md bg-transparent font-medium text-lg hover:bg-pink-50 transition block text-center smooth-scroll">
                            See all my other tours
                        </a>
                    </div>
                </div>
                @endif
                <div class="mt-8 mb-8">
                    <h2 class="text-2xl font-semibold mb-2 text-pink-600">About me</h2>
                    <hr class="mb-4">
                    <div class="text-base text-gray-900 leading-relaxed">
                        {!! nl2br(e($profile['about'] ?? $profile['description'] ?? 'No about me provided.')) !!}
                    </div>
                </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach(array_slice($galleryImages, 2) as $img)
                            <img src="{{ $img }}" alt="{{ $profile['name'] }} image" class="rounded-xl w-full h-48 object-cover gallery-img-clickable cursor-pointer">
                        @endforeach
                    </div>

                    <!-- Videos Section -->

@include('components.gallery-modal')
                    <section class="mt-12 overflow-hidden">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-2xl font-semibold mb-2 text-pink-600">Videos</h2>
                            <hr class="mb-4">
                            <!-- Gallery Video Link on next line -->
                            {{-- <div class="mt-2" x-data="{ open: false, currentIdx: 0, videos: [
                                'https://www.w3schools.com/html/mov_bbb.mp4',
                                'https://www.w3schools.com/html/movie.mp4',
                                'https://www.w3schools.com/html/mov_bbb.mp4',
                                'https://www.w3schools.com/html/movie.mp4',
                                'https://www.w3schools.com/html/mov_bbb.mp4',
                            ] }"> --}}
                                {{-- <button @click="open = true" class="px-4 py-1 rounded-full bg-pink-600 text-white font-bold text-base focus:outline-none hover:bg-pink-700 transition">Gallery Video</button> --}}
                                <!-- Gallery Video Modal -->
                                {{-- <div x-show="open" x-cloak x-effect="open ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
                                    <!-- Top Bar: Video Counter and Actions -->
                                    <div class="absolute top-0 left-0 w-full flex items-center justify-between px-4 py-2 bg-[#222] bg-opacity-95 z-20" style="min-height: 36px;">
                                        <span class="text-white text-lg font-normal tracking-wide select-none" x-text="(currentIdx + 1) + ' / ' + videos.length"></span>
                                        <div class="flex items-center gap-2">
                                            <button class="px-3 py-1 rounded-full bg-pink-600 text-white text-base font-semibold hover:bg-pink-700 transition focus:outline-none" @click.stop="$dispatch('gallery-action', { idx: currentIdx })">Action</button>
                                            <button @click.stop="open = false" class="text-gray-300 text-2xl font-bold hover:text-pink-500 transition focus:outline-none bg-white bg-opacity-10 rounded-full w-9 h-9 flex items-center justify-center shadow" aria-label="Close gallery">&times;</button>
                                        </div>
                                    </div>
                                    <!-- Overlay for closing -->
                                    <div class="absolute inset-0" @click="open = false"></div>
                                    <!-- Modal Content -->
                                    <div class="fixed inset-0 flex items-start justify-center h-full w-full z-30 shadow-2xl">
                                        <div class="flex items-start w-full h-full pt-16">
                                            <!-- Main Video -->
                                            <div class="flex-1 flex items-start justify-center pl-6">
                                                <template x-for="(vid, idx) in videos" :key="'main-' + idx">
                                                    <video x-show="currentIdx === idx" controls class="rounded-xl max-h-[60vh] max-w-full shadow-lg border-4 border-white object-contain bg-black">
                                                        <source :src="vid" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    <video
                                                        :src="video.src"
                                                        controls
                                                        x-pauseothers
                                                        class="rounded-xl w-full h-64 bg-black cursor-pointer object-cover border-4 border-transparent hover:border-pink-300 transition"
                                                        @click="$dispatch('open-video-modal', { idx })"
                                                        muted
                                                    ></video>
                                                        <source :src="vid" type="video/mp4">
                                                    </video>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
                            {{-- </div> --}}
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($profile['videos'] ?? [] as $videoUrl)
                            <video controls class="rounded-xl w-full h-64 bg-black">
                                <source src="{{ $videoUrl }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            @empty
                            <p class="text-gray-400 col-span-2">No videos available.</p>
                            @endforelse
                        </div>
                    </section>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('videoControl', {
            pauseOthers(current) {
                document.querySelectorAll('video').forEach(video => {
                    if (video !== current) video.pause();
                });
            }
        });
    });
</script>

<script>
    // Attach @play handler to all videos on the page (inline and in modals)
    document.addEventListener('alpine:init', () => {
        Alpine.directive('pauseothers', (el) => {
            el.addEventListener('play', () => {
                Alpine.store('videoControl').pauseOthers(el);
            });
        });
    });
</script>

                    <!-- My Upcoming Tours Section (Card Style) -->
                    <section id="upcoming-tours" class="mt-12 scroll-mt-32">
                        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                            <div class="flex items-center mb-1">
                                <i class="fa-solid fa-location-dot text-pink-600 text-xl mr-2"></i>
                                <h2 class="text-2xl font-semibold mb-2 text-pink-600">My upcoming tours</h2>
                                <hr class="mb-4">
                            </div>
                            <div class="border-b border-gray-200 mb-6"></div>
                            <div class="space-y-4">
                                @forelse($profile['tours'] ?? [] as $tour)
                                <div class="flex items-center">
                                    <span class="font-bold text-pink-600 text-base mr-4">{{ $tour['city'] }}</span>
                                    <span class="font-semibold text-gray-900 text-base">{{ $tour['from'] }} - {{ $tour['to'] }}</span>
                                </div>
                                @empty
                                <p class="text-gray-400">No upcoming tours scheduled.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    <!-- Contact Me For Section (Card Style) -->
                    <section id="contact-me-for" class="mt-12 scroll-mt-32">
                        @if(!empty($servicesProvided))
                        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                            <div class="flex items-center mb-1">
                                <i class="fa-solid fa-comments text-pink-600 text-xl mr-2"></i>
                                <h2 class="text-2xl font-semibold mb-2 text-pink-600">Contact me for</h2>
                                <hr class="mb-4">
                            </div>
                            <div class="border-b border-pink-300 mb-6 w-24"></div>
                            <ul class="space-y-2 list-none pl-0">
                                @foreach($servicesProvided as $item)
                                    <li class="flex items-start gap-2 text-lg">
                                        <span class="text-pink-600 text-xl mt-0.5">&raquo;</span>
                                        <span class="text-gray-900">{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </section>

                                    <!-- Short Link Section -->
                <div class="text-center mb-8 mt-8">
                    <span class="text-lg font-medium" style="background: linear-gradient(90deg, #d77dbb 0%, #6ec1e4 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: transparent;">
                        Find me easily with this short link:
                        <a href="{{ $profileUrl }}" class="hover:underline text-blue-500" style="background: none; color: #4fa3e3;">
                            {{ $profileUrlDisplay }}
                        </a>
                    </span>
                </div>
                </div>
                <!-- Info/Sidebar (right) -->
                <div class="flex flex-col gap-6">
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-bold text-lg text-black">Info</span>
                            @if($profile['is_verified'])
                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-xs font-semibold text-blue-700"><i class="fa-solid fa-badge-check text-blue-500 mr-1"></i> PHOTOS VERIFIED</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-y-3 gap-x-6 text-sm mb-3">
                            @if(!empty($profile['age']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-hourglass-half text-pink-600 w-5 text-center"></i>
                                <span>Age <span class="font-bold text-gray-900 ml-1">{{ $profile['age'] }}</span></span>
                            </div>
                            @endif
                            @if(!empty($profile['your_length']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-ruler-vertical text-pink-600 w-5 text-center"></i>
                                <span>Length <span class="font-bold text-gray-900 ml-1">{{ $profile['your_length'] }}</span></span>
                            </div>
                            @endif
                            @if(!empty($profile['bust_size']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-braille text-pink-600 w-5 text-center"></i>
                                <span>Bust Size <span class="font-bold text-gray-900 ml-1">{{ $profile['bust_size'] }}</span></span>
                            </div>
                            @endif
                            @if(!empty($profile['rate']))
                            <div class="flex items-center space-x-2 col-span-2">
                                <i class="fa-solid fa-dollar-sign text-pink-600 w-5 text-center"></i>
                                <span>Rate <span class="font-bold text-gray-900 ml-1">{{ $profile['rate'] }}</span></span>
                            </div>
                            @endif
                        </div>
                        <div class="mt-4">
                            <span class="block text-lg font-bold mb-1 text-black">Contact</span>
                            <div class="mb-2 text-sm p-2 text-gray-700">
                                Tell you saw advertisement in <span class="text-pink-600 font-semibold">HotEscort</span>, thanks!
                                @if(!empty($profile['contact_method']))
                                <br>Preferred contact method: <span class="font-semibold">{{ $profile['contact_method'] }}</span>
                                @endif
                            </div>
                            @if(!empty($primaryPhone))
                            <div class="flex items-center gap-2 mt-2">
                                <i class="fa-solid fa-mobile-screen text-blue-600 text-2xl"></i>
                                <span class="text-xs font-bold text-black">PHONE:</span>
                            </div>
                            <div class="text-2xl font-bold tracking-wide mb-2 text-black">{{ $primaryPhone }}</div>
                            @endif
                            @if(!empty($profile['website']))
                            <hr class="my-3">
                            <div class="flex items-center gap-2 mt-2">
                                <i class="fa-solid fa-globe text-blue-600 text-2xl"></i>
                                <span class="text-xs font-bold text-black">WEBSITE:</span>
                            </div>
                            <a href="{{ $profile['website'] }}" class="block text-pink-600 font-semibold text-base hover:underline break-all mb-2" target="_blank" rel="noopener noreferrer">{{ $profile['website'] }}</a>
                            @endif
                            @if(!empty($profile['onlyfans']))
                            <hr class="my-3">
                            <div class="flex items-center gap-2 mt-2">
                                <i class="fas fa-heart text-pink-600 text-2xl"></i>
                                <span class="text-xs font-bold text-black">ONLYFANS:</span>
                            </div>
                            <a href="{{ $profile['onlyfans'] }}" class="block text-pink-600 font-semibold text-base hover:underline break-all mb-2" target="_blank" rel="noopener noreferrer">{{ $profile['onlyfans'] }}</a>
                            @endif
                        </div>
                        <!-- Social Media Links -->
                        @if(!empty($profile['twitter']) || !empty($profile['whatsapp']))
                        <div class="mt-2">
                            <div class="flex items-center gap-2 mt-2">
                                <i class="fa-solid fa-share-nodes text-blue-600 text-2xl"></i>
                                <span class="text-xs font-bold text-black">SOCIAL MEDIA:</span>
                            </div>
                            <div class="flex gap-3 mt-2">
                                @if(!empty($profile['twitter']))
                                <a href="{{ $profile['twitter'] }}" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:underline" title="Twitter">
                                    <i class="fab fa-twitter-square fa-2x"></i>
                                </a>
                                @endif
                                @if(!empty($profile['whatsapp']))
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile['whatsapp']) }}" target="_blank" rel="noopener noreferrer" class="text-green-500 hover:underline" title="WhatsApp">
                                    <i class="fab fa-whatsapp-square fa-2x"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="flex gap-4 mt-4">
                            <button class="flex items-center gap-2 border border-gray-300 bg-white rounded-xl px-6 py-3 transition hover:bg-pink-50 text-pink-700 font-semibold text-lg w-1/2 justify-center" style="border-width:2px;">
                                <i class="fa-regular fa-heart text-2xl"></i>
                                <span class="font-semibold">Save favourite</span>
                            </button>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow p-4 border border-gray-100">
                        <h3 class="mb-2 text-lg font-bold text-pink-600 flex items-center gap-2">
                            <i class="fa-solid fa-user-gear text-pink-500"></i> My profile
                        </h3>
                        <hr class="mb-4">
                        <div class="grid grid-cols-2 gap-y-3 gap-x-6 text-sm">
                            @if(!empty($profile['ethnicity']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-globe text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Ethnicity</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['ethnicity'] }}</span>
                                </div>
                            </div>
                            @endif
                            @if(!empty($profile['hair_color']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-palette text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Hair color</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['hair_color'] }}</span>
                                </div>
                            </div>
                            @endif
                            @if(!empty($profile['hair_length']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-scissors text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Hair length</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['hair_length'] }}</span>
                                </div>
                            </div>
                            @endif
                            @if(!empty($profile['body_type']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-child-reaching text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Body type</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['body_type'] }}</span>
                                </div>
                            </div>
                            @endif
                            @if(!empty($profile['age_group']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-hourglass-half text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Age group</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['age_group'] }}</span>
                                </div>
                            </div>
                            @endif
                            @if(!empty($profile['bust_size']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-braille text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Bust size</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['bust_size'] }}</span>
                                </div>
                            </div>
                            @endif
                            @if(!empty($profile['your_length']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-ruler-vertical text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Length</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['your_length'] }}</span>
                                </div>
                            </div>
                            @endif
                            @if(!empty($profile['city']))
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-location-dot text-pink-600 w-5 text-center"></i>
                                <div>
                                    <span>Location</span><br>
                                    <span class="font-bold text-gray-900">{{ $profile['city'] }}{{ !empty($profile['state']) ? ', ' . $profile['state'] : '' }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if(!empty($profileTags))
                        <div class="flex flex-col gap-y-2 mt-6">
                            <div class="flex flex-wrap gap-x-2">
                                @foreach(array_chunk($profileTags, 2) as $row)
                                    <div class="flex gap-x-2 mb-1">
                                        @foreach($row as $tag)
                                            <span class="px-4 py-1 bg-pink-600 text-white rounded-full text-base font-bold" style="line-height:1.2;">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="bg-white rounded-2xl shadow p-4 border border-gray-100">
                        <h3 class="mb-2 text-lg font-bold flex items-center gap-2 text-pink-600">
                            <i class="fa-regular fa-clock text-pink-600"></i> Rates
                        </h3>
                        <hr class="mb-3">
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left font-bold text-black">Session</th>
                                        <th class="px-4 py-2 text-left font-bold text-black">Outcall</th>
                                        <th class="px-4 py-2 text-left font-bold text-black">In-call</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($profile['price_list'] ?? [] as $i => $rate)
                                    @php
                                        $sessionLabel = $rate['description'] ?: ($rate['group'] ?: '—');
                                    @endphp
                                    <tr class="{{ $i % 2 === 0 ? 'bg-gray-100' : '' }}">
                                        <td class="px-4 py-2 font-normal text-black">{{ $sessionLabel }}</td>
                                        <td class="px-4 py-2 font-bold text-black">{{ $rate['outcall'] ?: '—' }}</td>
                                        <td class="px-4 py-2 font-bold text-black">{{ $rate['incall'] ?: '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-gray-400 text-center">Contact for rates</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- My Availability Section -->
                    <div class="bg-white rounded-2xl shadow p-4 border border-gray-100 mt-6">
                        <h3 class="mb-2 text-lg font-bold flex items-center gap-2 text-pink-600">
                            <i class="fa-regular fa-calendar-days text-pink-600"></i> My availability
                        </h3>
                        <hr class="mb-3">
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left font-bold text-black">Day</th>
                                        <th class="px-4 py-2 text-left font-bold text-black">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($profile['availability_list'] ?? [] as $i => $avail)
                                    <tr class="{{ $i % 2 === 0 ? 'bg-gray-100' : '' }}">
                                        <td class="px-4 py-2 font-normal text-black">{{ $avail['day'] }}</td>
                                        <td class="px-4 py-2 font-bold text-black">{{ $avail['time'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-2 text-gray-400 text-center">No availability set</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>


                      <button class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-2 text-gray-700 font-semibold hover:bg-gray-50 transition"><i class="fa-regular fa-flag"></i> Report User</button>
                </div>
            </div>
        </div>

        <section class="mt-12 overflow-hidden">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-semibold mb-2 text-pink-600">Nearby listings</h2>
                <hr class="mb-4">
                <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">View all →</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($nearbyProfiles as $nearby)
                    <a href="{{ route('profile.show', array_merge(['slug' => $nearby['slug']], request()->query())) }}" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <img src="{{ $nearby['image'] }}" alt="{{ $nearby['name'] }}" class="h-48 w-full object-cover">
                        <div class="p-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $nearby['name'] }}</h3>
                            <p class="text-xs text-gray-500">{{ $nearby['city'] }} • {{ $nearby['service_1'] }}</p>
                            <p class="mt-2 text-base font-bold text-gray-900">{{ $nearby['rate'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    html {
        scroll-behavior: smooth;
    }
</style>
<style>
    .gallery-scroll {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
    }

    .gallery-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .gallery-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 9999px;
    }
</style>
@endpush


