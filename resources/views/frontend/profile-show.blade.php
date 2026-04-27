@extends('layouts.frontend')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/profile-show.css') }}">
@endpush

@section('title', $profile['name'] . ' Profile')

@php
$profileTags = array_values(array_unique(array_merge(
    is_array($profile['primary_identity'] ?? null) ? $profile['primary_identity'] : [],
    is_array($profile['attributes'] ?? null) ? $profile['attributes'] : [],
    is_array($profile['services_style'] ?? null) ? $profile['services_style'] : [],
)));
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-800 profile-page-content"
    x-data="favouriteBookmark({ favourites: {{ Js::from($userFavourites ?? []) }} })"
>
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
            $introText = strip_tags($profile['introduction_line'] ?? '');
            if (!empty($profile['age']) && !empty($introText)) {
                $introTagline = $profile['age'] . ' - ' . $introText;
            } elseif (!empty($profile['age'])) {
                $introTagline = (string) $profile['age'];
            } elseif (!empty($introText)) {
                $introTagline = $introText;
            }
        @endphp

        <div class="max-w-5xl mx-auto">
                <div class="text-center mb-12">
                    @if($availableNow)
                    <div class="inline-block mb-4 px-6 py-2 rounded bg-[#e13a8b] text-white font-extrabold text-base tracking-wide" style="letter-spacing:0.5px;">
                        AVAILABLE NOW{{ $availableTillText }}
                    </div>
                    @elseif($isOnline)
                    <div class="inline-block mb-4 px-6 py-2 rounded bg-green-500 text-white font-extrabold text-base tracking-wide" style="letter-spacing:0.5px;">
                        ONLINE NOW
                    </div>
                    @endif
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-pink-600 mb-3" style="color:#e13a8b;">
                        {{ $profile['name'] }}
                    </h1>
                    @if(!empty($profile['city']))
                        <div class="flex items-center justify-center mt-2 mb-3">
                            <span class="text-base font-semibold text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-pink-400"></i>
                                {{ $profile['suburb'] }}
                            </span>
                        </div>
                    @endif
                    @if(!empty($introTagline))
                    <div class="mt-3 text-lg text-gray-700 font-medium">{{ $introTagline }}</div>
                    @endif
                </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">


                <!-- Gallery (left, spans 2 columns) -->
                <div class="md:col-span-2 flex flex-col gap-4 relative order-2 md:order-1">
                    <!-- Previous Button (left corner) -->
                          <a href="{{ route('profile.show', ['slug' => $prevProfile['slug']]) }}"
                              x-data="{ visible: false }"
                              x-show="visible"
                              x-cloak
                              x-transition:enter="transition duration-300"
                              x-transition:enter-start="opacity-0 scale-90"
                              x-transition:enter-end="opacity-100 scale-100"
                              x-transition:leave="transition duration-200"
                              x-transition:leave-start="opacity-100 scale-100"
                              x-transition:leave-end="opacity-0 scale-90"
                              @scroll.window.passive="visible = window.scrollY > 300 && (document.getElementById('main-footer')?.getBoundingClientRect().top ?? Infinity) > window.innerHeight"
                              class="md:fixed md:left-0 md:top-1/2 md:-translate-y-1/2 z-30 flex flex-col items-center group mobile-nav-btn-wrapper mobile-prev-btn"
                              style="margin-left: 0.5rem;">
                        <div class="rounded-xl p-0.5 bg-white shadow-lg border border-pink-200">
                            <button class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded-xl flex flex-col items-center shadow-lg min-w-[100px] min-h-[60px] mobile-transparent-nav-btn">
                                <span class="flex items-center"><i class="fa-solid fa-arrow-left text-xl mr-2"></i> <span class="text-xs font-semibold">PREVIOUS</span></span>
                                <span class="text-base font-extrabold mt-0.5">{{ $prevProfile['name'] }}</span>
                            </button>
                        </div>
                    </a>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach(array_slice($galleryImages, 0, 2) as $img)
                            <img src="{{ $img }}" alt="{{ $profile['name'] }} image" class="rounded-xl w-full h-64 object-cover gallery-img-clickable cursor-pointer" loading="lazy" decoding="async">
                        @endforeach
                    </div>
                        <!-- Next Button (right corner) -->
                                <a href="{{ route('profile.show', ['slug' => $nextProfile['slug']]) }}"
                                    x-data="{ visible: false }"
                                    x-show="visible"
                                    x-cloak
                                    x-transition:enter="transition duration-300"
                                    x-transition:enter-start="opacity-0 scale-90"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition duration-200"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-90"
                                    @scroll.window.passive="visible = window.scrollY > 300 && (document.getElementById('main-footer')?.getBoundingClientRect().top ?? Infinity) > window.innerHeight"
                                    class="md:fixed md:right-0 md:top-1/2 md:-translate-y-1/2 z-30 flex flex-col items-center group mobile-nav-btn-wrapper mobile-next-btn"
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
@php
                        $safeAbout = strip_tags(
                            (string) ($profile['about'] ?? $profile['description'] ?? ''),
                            '<p><br><ul><ol><li><strong><em><blockquote>'
                        );
                    @endphp
                    @if(!empty($safeAbout))
                        <div class="mt-8 mb-8">
                            <h2 class="text-2xl font-semibold mb-2 text-pink-600">About me</h2>
                            <hr class="mb-4">
                            <div class="text-base text-gray-900 leading-relaxed break-words overflow-hidden [&_*]:max-w-full">
                                {!! nl2br($safeAbout) !!}
                            </div>
                            <br>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach(array_slice($galleryImages, 2) as $img)
                            <img src="{{ $img }}" alt="{{ $profile['name'] }} image" class="rounded-xl w-full h-64 object-cover gallery-img-clickable cursor-pointer" loading="lazy" decoding="async">
                        @endforeach
                    </div>
                    <br>

                    <!-- Videos Section -->

@include('components.gallery-modal')
                    @if(!empty($profile['videos'] ?? []))
                    <section class="mt-12 overflow-hidden">
                        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-2xl font-semibold mb-2 text-pink-600">Videos</h2>
                            <hr class="mb-4">
                        </div>
                        <br>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
                            x-data="{
                                init() {
                                    // Add global video pause handler
                                    document.addEventListener('play', (e) => {
                                        if (e.target.tagName === 'VIDEO') {
                                            document.querySelectorAll('video').forEach(video => {
                                                if (video !== e.target) {
                                                    video.pause();
                                                }
                                            });
                                        }
                                    }, true);
                                }
                            }">
                            @foreach($profile['videos'] ?? [] as $videoUrl)
                            <div class="relative" x-data="{ playing: false }">
                                <video controls preload="metadata" class="rounded-xl w-full h-64 bg-black object-cover"
                                    x-on:play="playing = true"
                                    x-on:pause="playing = false"
                                    x-on:ended="playing = false"
                                    x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='block'; playing = false"
                                    poster="https://picsum.photos/400/225?random=1">
                                    <source src="{{ $videoUrl }}" type="video/mp4">
                                    <source src="{{ $videoUrl }}" type="video/webm">
                                    <source src="{{ $videoUrl }}" type="video/ogg">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none hidden">
                                    <div class="bg-red-500 bg-opacity-75 text-white px-4 py-2 rounded">
                                        <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                                        Video unavailable
                                    </div>
                                </div>
                                <div x-show="!playing" x-transition class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div class="bg-black bg-opacity-50 rounded-full p-3 opacity-75">
                                        <i class="fa-solid fa-play text-white text-xl"></i>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

@push('scripts')
<script src="{{ asset('frontend/js/profile-show.js') }}"></script>
@endpush

                    <!-- My Upcoming Tours Section (Card Style) -->
                    @if(!empty($profile['tours'] ?? []))
                    <section id="upcoming-tours" class="mt-12 scroll-mt-32">
                        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                            <div class="flex items-center mb-1">
                                <i class="fa-solid fa-location-dot text-pink-600 text-xl mr-2"></i>
                                <h2 class="text-2xl font-semibold mb-2 text-pink-600">My upcoming tours</h2>
                                <hr class="mb-4">
                            </div>
                            <div class="border-b border-gray-200 mb-6"></div>
                            <div class="space-y-4">
                                @foreach($profile['tours'] ?? [] as $tour)
                                <div class="flex items-center">
                                    <span class="font-bold text-pink-600 text-base mr-4">{{ $tour['city'] }}</span>
                                    <span class="font-semibold text-gray-900 text-base">{{ $tour['from'] }} - {{ $tour['to'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                    <br>
                    @endif

                    <!-- Profile Message Section -->
                    @if(!empty($profile['profile_message']))
                    <section class="mt-12">
                        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                            <div class="flex items-center mb-3">
                                <i class="fa-solid fa-bullhorn text-pink-600 text-xl mr-2"></i>
                                <h2 class="text-2xl font-semibold text-pink-600">Message from {{ $profile['name'] }}</h2>
                            </div>
                            <div class="border-b border-gray-200 mb-4"></div>
                            <div class="prose max-w-none text-gray-700 leading-relaxed">
                                {!! nl2br($profile['profile_message']) !!}
                            </div>
                        </div>
                    </section>
                    <br>
                    @endif

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
                        <br>
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
                <div class="flex flex-col gap-6 order-1 md:order-2">
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-bold text-lg text-black">Info</span>
                            @if($profile['is_verified'])
                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-xs font-semibold text-blue-700"><i class="fa-solid fa-badge-check text-blue-500 mr-1"></i> PHOTOS VERIFIED</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm mb-3">
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
                        @if(!empty($primaryPhone) || !empty($profile['website']) || !empty($profile['onlyfans']) || !empty($profile['contact_method']))
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
                            <a href="tel:{{ $phoneHref }}" aria-label="Call {{ $primaryPhone }}" class="block text-2xl font-bold tracking-wide mb-2 text-black hover:text-pink-600 transition">{{ $primaryPhone }}</a>
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
                        @endif
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
                        <div class="flex flex-col sm:flex-row gap-4 mt-4">
                            <button
                                type="button"
                                @click.prevent="toggleFavourite('{{ $profile['slug'] }}')"
                                :class="isFavourite('{{ $profile['slug'] }}') ? 'bg-pink-50 text-pink-700 border-pink-400' : 'bg-white text-pink-700 border-gray-300 hover:bg-pink-50'"
                                class="flex items-center gap-2 border rounded-xl px-6 py-3 transition font-semibold text-lg w-full justify-center"
                                style="border-width:2px;"
                                title="Save favourite"
                                aria-label="Save favourite"
                            >
                                <i
                                    :class="isFavourite('{{ $profile['slug'] }}') ? 'fa-solid fa-heart text-pink-600' : 'fa-regular fa-heart text-pink-600'"
                                    class="fa-regular fa-heart text-pink-600 text-2xl"
                                    aria-hidden="true"
                                ></i>
                                <span
                                    class="font-semibold uppercase tracking-wide text-pink-700"
                                    x-text="isFavourite('{{ $profile['slug'] }}') ? 'SAVED FAVOURITE' : 'SAVE FAVOURITE'"
                                >SAVE FAVOURITE</span>
                            </button>
                        </div>
                    </div>
                    @if(!empty($profile['ethnicity']) || !empty($profile['hair_color']) || !empty($profile['hair_length']) || !empty($profile['body_type']) || !empty($profile['age_group']) || !empty($profile['bust_size']) || !empty($profile['your_length']) || !empty($profile['city']) || !empty($profileTags))
                    <div class="bg-white rounded-2xl shadow p-4 border border-gray-100">
                        <h3 class="mb-2 text-lg font-bold text-pink-600 flex items-center gap-2">
                            <i class="fa-solid fa-user-gear text-pink-500"></i> My profile
                        </h3>
                        <hr class="mb-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
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
                        <div class="flex flex-wrap gap-2 mt-6">
                            @foreach($profileTags as $tag)
                                @if(!empty($tag))
                                <span class="px-4 py-1 bg-pink-600 text-white rounded-full text-sm font-semibold" style="line-height:1.2;">{{ $tag }}</span>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                    @php
                        $nonEmptyRates = array_filter($profile['price_list'] ?? [], function ($rate) {
                            return !empty($rate['outcall']) || !empty($rate['incall']);
                        });
                    @endphp
                    @if(!empty($nonEmptyRates))
                    <div class="bg-white rounded-2xl shadow p-4 border border-gray-100">
                        <h3 class="mb-2 text-lg font-bold flex items-center gap-2 text-pink-600">
                            <i class="fa-regular fa-clock text-pink-600"></i> Rates
                        </h3>
                        <hr class="mb-3">
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left font-bold text-black">Session</th>
                                        <th class="px-4 py-2 text-left font-bold text-black">Outcall</th>
                                        <th class="px-4 py-2 text-left font-bold text-black">In-call</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nonEmptyRates as $i => $rate)
                                    @php
                                        $sessionLabel = $rate['description'] ?: ($rate['group'] ?: 'Session');
                                    @endphp
                                    <tr class="{{ $i % 2 === 0 ? 'bg-gray-100' : '' }}">
                                        <td class="px-4 py-2 font-normal text-black">{{ $sessionLabel }}</td>
                                        <td class="px-4 py-2 font-bold text-black">{{ $rate['outcall'] ?: 'N/A' }}</td>
                                        <td class="px-4 py-2 font-bold text-black">{{ $rate['incall'] ?: 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    @php
                        $nonEmptyAvailability = array_filter($profile['availability_list'] ?? [], function ($avail) {
                            return !empty($avail['time']) && $avail['time'] !== 'Unavailable';
                        });
                    @endphp
                    @if(!empty($nonEmptyAvailability))
                    <!-- My Availability Section -->
                    <div class="bg-white rounded-2xl shadow p-4 border border-gray-100 mt-6">
                        <h3 class="mb-2 text-lg font-bold flex items-center gap-2 text-pink-600">
                            <i class="fa-regular fa-calendar-days text-pink-600"></i> My availability
                        </h3>
                        <hr class="mb-3">
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left font-bold text-black">Day</th>
                                        <th class="px-4 py-2 text-left font-bold text-black">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nonEmptyAvailability as $i => $avail)
                                    <tr class="{{ $i % 2 === 0 ? 'bg-gray-100' : '' }}">
                                        <td class="px-4 py-2 font-normal text-black">{{ $avail['day'] }}</td>
                                        <td class="px-4 py-2 font-bold text-black">{{ $avail['time'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif


                      <button
                            onclick="document.getElementById('report-modal').classList.remove('hidden')"
                            class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-2 text-gray-700 font-semibold hover:bg-gray-50 transition">
                            <i class="fa-regular fa-flag"></i> Report User
                        </button>
                </div>
            </div>
        </div>

        <section x-data="{
                page: 0,
                pageSize: 1,
                total: {{ count($nearbyProfiles) }},
                get pages() { return Math.max(1, Math.ceil(this.total / this.pageSize)); },
                init() { this.updatePageSize(); },
                updatePageSize() {
                    this.pageSize = window.innerWidth >= 1024 ? 4 : window.innerWidth >= 640 ? 2 : 1;
                    if (this.page > this.pages - 1) {
                        this.page = this.pages - 1;
                    }
                },
                prev() { if (this.page > 0) this.page--; },
                next() { if (this.page < this.pages - 1) this.page++; }
            }"
            @resize.window="updatePageSize()"
            class="mt-16 overflow-hidden"
        >
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-1">
                    <h2 class="text-2xl font-semibold text-pink-600">Nearby listings</h2>
                    <span class="text-sm text-gray-500">Showing {{ count($nearbyProfiles) }} profiles</span>
                </div>
                <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">View all →</a>
            </div>

            @if(count($nearbyProfiles) > 0)
            <div class="relative group">
                <div class="overflow-hidden px-4 sm:px-6 pb-2">
                    <div class="flex flex-nowrap gap-4 transition-transform duration-500"
                        :style="`transform: translateX(-${page * 100}%);`
                    ">
                        @foreach($nearbyProfiles as $nearby)
                            <article class="group relative flex-none min-w-full sm:min-w-[calc(50%-0.75rem)] lg:min-w-[calc(25%-0.75rem)] overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md hover:border-gray-300 hover:-translate-y-0.5">
                                <a href="{{ route('profile.show', array_merge(['slug' => $nearby['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $nearby['name'] }}"></a>

                                <div class="relative overflow-hidden rounded-t-2xl">
                                    @if(!empty($nearby['image']))
                                        <img src="{{ $nearby['image'] }}" alt="{{ $nearby['name'] }}" class="w-full object-cover origin-center transition-transform duration-500 group-hover:scale-105 h-52" loading="lazy" decoding="async">
                                    @else
                                        <div class="flex items-center justify-center bg-gray-100 text-gray-400 h-52">
                                            <i class="fa-solid fa-image text-4xl"></i>
                                        </div>
                                    @endif

                                    <div class="absolute left-0 top-3 z-10 flex flex-col gap-1">
                                        @if(!empty($nearby['verified']))
                                            <span class="inline-flex items-center gap-1 bg-cyan-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0;">
                                                <i class="fa-solid fa-camera text-[9px]"></i> Photo Verified
                                            </span>
                                        @endif
                                        @if(!empty($nearby['active']))
                                            <span class="inline-flex items-center gap-1 bg-emerald-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0;">
                                                <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Online Now
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-3.5">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-[11px] text-gray-400">{{ $nearby['date'] }}</span>
                                    </div>

                                    <h3 class="text-sm font-medium text-gray-800 truncate">
                                        {{ $nearby['name'] }}@if(!empty($nearby['suburb'])) <span class="text-gray-400 font-normal">({{ $nearby['suburb'] }})</span>@endif
                                    </h3>

                                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $nearby['rate'] }}</p>

                                    @if(!empty($nearby['in_call']) || !empty($nearby['out_call']))
                                        <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-[11px]">
                                            @if(!empty($nearby['in_call']))
                                                <span class="inline-flex items-center gap-1 text-gray-600">
                                                    <i class="fa-solid fa-house text-emerald-500 text-[10px]" aria-hidden="true"></i>
                                                    <span class="font-medium">In:</span> {{ $nearby['in_call'] }}
                                                </span>
                                            @endif
                                            @if(!empty($nearby['out_call']))
                                                <span class="inline-flex items-center gap-1 text-gray-600">
                                                    <i class="fa-solid fa-car text-blue-500 text-[10px]" aria-hidden="true"></i>
                                                    <span class="font-medium">Out:</span> {{ $nearby['out_call'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mt-3 flex flex-wrap items-start gap-x-4 gap-y-1.5 text-[12px] text-gray-600">
                                        @if(!empty($nearby['city']) || !empty($nearby['suburb']))
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fa-solid fa-location-dot text-pink-500 text-[11px]"></i>
                                                {{ $nearby['suburb'] ?: $nearby['city'] }}
                                            </span>
                                        @endif
                                        @if(!empty($nearby['service_1']))
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fa-solid fa-briefcase text-gray-400 text-[11px]"></i>
                                                {{ $nearby['service_1'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!empty($nearby['service_2']) || !empty($nearby['description']))
                                        <div class="mt-2 text-[12px] text-gray-600 line-clamp-2">
                                            <i class="fa-solid fa-gem text-blue-500 text-[10px] mr-1"></i>
                                            {{ !empty($nearby['service_2']) ? $nearby['service_2'] : $nearby['description'] }}
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <!-- Left Arrow -->
                <button type="button"
                    @click="prev()"
                    :disabled="page === 0"
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center rounded-full bg-white border-2 border-pink-500 shadow-lg text-pink-600 hover:bg-pink-500 hover:text-white transition"
                    :class="page === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:scale-110'"
                    title="Previous"
                >
                    <i class="fa-solid fa-chevron-left text-lg"></i>
                </button>

                <!-- Right Arrow -->
                <button type="button"
                    @click="next()"
                    :disabled="page >= pages - 1"
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center rounded-full bg-white border-2 border-pink-500 shadow-lg text-pink-600 hover:bg-pink-500 hover:text-white transition"
                    :class="page >= pages - 1 ? 'opacity-40 cursor-not-allowed' : 'hover:scale-110'"
                    title="Next"
                >
                    <i class="fa-solid fa-chevron-right text-lg"></i>
                </button>
            </div>
            @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-users text-gray-400 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No nearby listings found</h3>
                        <p class="text-gray-600 text-sm mb-4">There are currently no other providers in your area.</p>
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded-lg font-medium transition">
                            <i class="fa-solid fa-search"></i>
                            Browse all listings
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </section>
    </div>
</div>

@endsection

@push('scripts')
<!-- Report User Modal -->
<div id="report-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 relative">
        <button onclick="document.getElementById('report-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-bold leading-none">&times;</button>
        <h2 class="text-xl font-bold text-pink-600 mb-1 flex items-center gap-2"><i class="fa-regular fa-flag"></i> Report Profile</h2>
        <p class="text-sm text-gray-500 mb-4">Help us keep the community safe. All reports are reviewed by our admin team.</p>

        <div id="report-success" class="hidden mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
            Thank you! Your report has been submitted and will be reviewed by our team.
        </div>
        <div id="report-error" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm"></div>

        <form id="report-form" onsubmit="submitReport(event)">
            @csrf
            <input type="hidden" name="provider_profile_id" value="{{ $profile['id'] }}">

            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Your Name <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="reporter_name" placeholder="Enter your name" maxlength="255"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Your Email <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="email" name="reporter_email" placeholder="Enter your email" maxlength="255"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <select name="reason" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300">
                    <option value="" disabled selected>Select a reason</option>
                    <option value="spam">Spam</option>
                    <option value="fake_profile">Fake Profile</option>
                    <option value="inappropriate_content">Inappropriate Content</option>
                    <option value="harassment">Harassment</option>
                    <option value="scam">Scam</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Additional Details <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="3" placeholder="Provide any additional details..." maxlength="2000"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('report-modal').classList.add('hidden')"
                    class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" id="report-submit-btn"
                    class="flex-1 bg-pink-600 hover:bg-pink-700 text-white rounded-xl px-4 py-2 text-sm font-semibold transition">
                    Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    if (typeof window.favouriteBookmark !== 'function') {
        window.favouriteBookmark = function favouriteBookmark(config = {}) {
            return {
                favourites: Array.isArray(config.favourites) ? config.favourites.map(String) : [],

                init() {
                    const stored = window.localStorage.getItem('profile_favourites');

                    if (stored) {
                        try {
                            const parsed = JSON.parse(stored);

                            if (Array.isArray(parsed)) {
                                this.favourites = parsed.map(String);
                            }
                        } catch (error) {
                            window.localStorage.removeItem('profile_favourites');
                        }
                    }
                },

                normalise(slug) {
                    return String(slug || '').trim();
                },

                isFavourite(slug) {
                    slug = this.normalise(slug);
                    return this.favourites.map(String).includes(slug);
                },

                toggleFavourite(slug) {
                    slug = this.normalise(slug);

                    if (!slug) {
                        return;
                    }

                    if (this.isFavourite(slug)) {
                        this.favourites = this.favourites.filter(item => String(item) !== slug);
                    } else {
                        this.favourites.push(slug);
                    }

                    window.localStorage.setItem('profile_favourites', JSON.stringify(this.favourites));
                }
            };
        };
    }
</script>

<script>
    window.__profileShowConfig = {
        reportUrl: '{{ route('profile.report') }}',
        profileId: {{ $profile['id'] }}
    };

    window.submitReport = async function submitReport(event) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = document.getElementById('report-submit-btn');
        const successDiv = document.getElementById('report-success');
        const errorDiv = document.getElementById('report-error');

        if (submitBtn) submitBtn.disabled = true;
        if (successDiv) successDiv.classList.add('hidden');
        if (errorDiv) { errorDiv.classList.add('hidden'); errorDiv.textContent = ''; }

        const formData = new FormData(form);

        try {
            const response = await fetch(window.__profileShowConfig.reportUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            let data = {};
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = { message: await response.text() || 'Unexpected server response.' };
            }

            if (response.ok) {
                if (successDiv) successDiv.classList.remove('hidden');
                form.reset();
                setTimeout(function () {
                    const modal = document.getElementById('report-modal');
                    if (modal) modal.classList.add('hidden');
                    if (successDiv) successDiv.classList.add('hidden');
                }, 2000);
            } else {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : (data.message || 'An error occurred. Please try again.');
                if (errorDiv) { errorDiv.textContent = msg; errorDiv.classList.remove('hidden'); }
            }
        } catch (error) {
            if (errorDiv) { errorDiv.textContent = 'A network error occurred. Please try again.'; errorDiv.classList.remove('hidden'); }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    };
</script>


@endpush
