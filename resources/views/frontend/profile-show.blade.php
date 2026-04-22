@extends('layouts.frontend')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/profile-show.css') }}">

<style>
    [x-cloak] {
        display: none !important;
    }

    .profile-page-content video {
        display: block;
        width: 100%;
        height: 100%;
    }

    .mobile-safe-card {
        min-width: 0;
        overflow: hidden;
    }

    .profile-content-html,
    .profile-content-html * {
        max-width: 100%;
        word-wrap: break-word;
    }

    .profile-content-html img,
    .profile-content-html iframe,
    .profile-content-html video {
        max-width: 100%;
        height: auto;
    }

    .video-card {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        background: #000;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .video-shell {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        background: #000;
    }

    .video-loader,
    .video-error,
    .video-play-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.25s ease;
    }

    .video-loader {
        background: rgba(0, 0, 0, 0.35);
        z-index: 3;
    }

    .video-play-overlay {
        pointer-events: none;
        z-index: 2;
    }

    .video-error {
        background: rgba(0, 0, 0, 0.75);
        z-index: 4;
        padding: 1rem;
        text-align: center;
    }

    .video-loader-spinner {
        width: 42px;
        height: 42px;
        border-radius: 9999px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 767px) {
        .mobile-nav-fixed {
            position: static !important;
            transform: none !important;
            margin: 0 !important;
            width: 100%;
        }

        .mobile-nav-fixed > div {
            width: 100%;
        }

        .mobile-nav-fixed button {
            width: 100%;
            min-width: 0 !important;
            min-height: 52px !important;
        }
    }
</style>
@endpush

@section('title', $profile['name'] . ' Profile')

@php
    $profileTags = array_values(array_unique(array_merge(
        is_array($profile['primary_identity'] ?? null) ? $profile['primary_identity'] : [],
        is_array($profile['attributes'] ?? null) ? $profile['attributes'] : [],
        is_array($profile['services_style'] ?? null) ? $profile['services_style'] : [],
    )));

    $primaryPhone = trim((string) ($profile['phone'] ?? $profile['whatsapp'] ?? ''));
    $phoneHref = preg_replace('/[^0-9+]/', '', $primaryPhone);

    $galleryImages = !empty($profile['images'])
        ? $profile['images']
        : (!empty($profile['image']) ? [$profile['image']] : []);

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

    $safeAbout = strip_tags(
        (string) ($profile['about'] ?? $profile['description'] ?? ''),
        '<p><br><ul><ol><li><strong><em><blockquote>'
    );

    $nonEmptyRates = array_filter($profile['price_list'] ?? [], function ($rate) {
        return !empty($rate['outcall']) || !empty($rate['incall']);
    });

    $nonEmptyAvailability = array_filter($profile['availability_list'] ?? [], function ($avail) {
        return !empty($avail['time']) && $avail['time'] !== 'Unavailable';
    });

    $videos = $profile['videos'] ?? [];
    $tours = $profile['tours'] ?? [];
@endphp

@section('content')
<div
    class="min-h-screen bg-gray-50 text-gray-800 profile-page-content"
    x-data="profilePage({ favourites: {{ Js::from($userFavourites ?? []) }} })"
    x-init="init()"
>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-700">Home</a>
            <span>›</span>
            <span>Listings</span>
            <span>›</span>
            <span class="text-gray-700">{{ $profile['name'] }}</span>
        </div>

        <div class="mx-auto max-w-6xl">
            <div class="mb-8 text-center sm:mb-12">
                @if($availableNow)
                    <div class="mb-4 inline-block rounded bg-[#e13a8b] px-4 py-2 text-sm font-extrabold tracking-wide text-white sm:px-6 sm:text-base" style="letter-spacing:0.5px;">
                        AVAILABLE NOW{{ $availableTillText }}
                    </div>
                @elseif($isOnline)
                    <div class="mb-4 inline-block rounded bg-green-500 px-4 py-2 text-sm font-extrabold tracking-wide text-white sm:px-6 sm:text-base" style="letter-spacing:0.5px;">
                        ONLINE NOW
                    </div>
                @endif

                <h1 class="mb-3 text-2xl font-extrabold text-pink-600 sm:text-4xl" style="color:#e13a8b;">
                    {{ $profile['name'] }}
                </h1>

                @if(!empty($profile['city']))
                    <div class="mt-2 mb-3 flex items-center justify-center">
                        <span class="flex items-center gap-1 text-sm font-semibold text-gray-500 sm:text-base">
                            <i class="fa-solid fa-location-dot text-pink-400"></i>
                            {{ $profile['suburb'] }}
                        </span>
                    </div>
                @endif

                @if(!empty($introTagline))
                    <div class="mt-3 text-base font-medium text-gray-700 sm:text-lg">{{ $introTagline }}</div>
                @endif
            </div>

            <div class="mb-4 grid grid-cols-2 gap-3 md:hidden">
                @if(!empty($prevProfile['slug']))
                    <a href="{{ route('profile.show', ['slug' => $prevProfile['slug']]) }}" class="block">
                        <div class="rounded-xl border border-pink-200 bg-white p-1 shadow">
                            <button type="button" class="flex min-h-[52px] w-full flex-col items-center justify-center rounded-xl bg-pink-500 px-3 py-2 text-white">
                                <span class="flex items-center text-[11px] font-semibold">
                                    <i class="fa-solid fa-arrow-left mr-2"></i> PREVIOUS
                                </span>
                                <span class="mt-0.5 line-clamp-1 text-sm font-extrabold">{{ $prevProfile['name'] }}</span>
                            </button>
                        </div>
                    </a>
                @endif

                @if(!empty($nextProfile['slug']))
                    <a href="{{ route('profile.show', ['slug' => $nextProfile['slug']]) }}" class="block">
                        <div class="rounded-xl border border-pink-200 bg-white p-1 shadow">
                            <button type="button" class="flex min-h-[52px] w-full flex-col items-center justify-center rounded-xl bg-pink-500 px-3 py-2 text-white">
                                <span class="flex items-center text-[11px] font-semibold">
                                    NEXT <i class="fa-solid fa-arrow-right ml-2"></i>
                                </span>
                                <span class="mt-0.5 line-clamp-1 text-sm font-extrabold">{{ $nextProfile['name'] }}</span>
                            </button>
                        </div>
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
                <div class="order-1 flex min-w-0 flex-col gap-6 lg:col-span-2">

                    @if(!empty($prevProfile['slug']))
                        <a
                            href="{{ route('profile.show', ['slug' => $prevProfile['slug']]) }}"
                            x-data="{ visible: false }"
                            x-show="visible"
                            x-cloak
                            x-transition:enter="transition duration-300"
                            x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-90"
                            @scroll.window.passive="visible = window.innerWidth >= 768 && window.scrollY > 300 && (document.getElementById('main-footer')?.getBoundingClientRect().top ?? Infinity) > window.innerHeight"
                            class="mobile-nav-fixed hidden md:fixed md:left-3 md:top-1/2 md:z-30 md:flex md:-translate-y-1/2 md:flex-col md:items-center"
                        >
                            <div class="rounded-xl border border-pink-200 bg-white p-0.5 shadow-lg">
                                <button type="button" class="flex min-h-[60px] min-w-[110px] flex-col items-center rounded-xl bg-pink-500 px-4 py-2 font-bold text-white shadow-lg hover:bg-pink-600">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-arrow-left mr-2 text-xl"></i>
                                        <span class="text-xs font-semibold">PREVIOUS</span>
                                    </span>
                                    <span class="mt-0.5 text-base font-extrabold">{{ $prevProfile['name'] }}</span>
                                </button>
                            </div>
                        </a>
                    @endif

                    @if(!empty($nextProfile['slug']))
                        <a
                            href="{{ route('profile.show', ['slug' => $nextProfile['slug']]) }}"
                            x-data="{ visible: false }"
                            x-show="visible"
                            x-cloak
                            x-transition:enter="transition duration-300"
                            x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-90"
                            @scroll.window.passive="visible = window.innerWidth >= 768 && window.scrollY > 300 && (document.getElementById('main-footer')?.getBoundingClientRect().top ?? Infinity) > window.innerHeight"
                            class="mobile-nav-fixed hidden md:fixed md:right-3 md:top-1/2 md:z-30 md:flex md:-translate-y-1/2 md:flex-col md:items-center"
                        >
                            <div class="rounded-xl border border-pink-200 bg-white p-0.5 shadow-lg">
                                <button type="button" class="flex min-h-[60px] min-w-[110px] flex-col items-center rounded-xl bg-pink-500 px-4 py-2 font-bold text-white shadow-lg hover:bg-pink-600">
                                    <span class="flex items-center">
                                        <span class="text-xs font-semibold">NEXT</span>
                                        <i class="fa-solid fa-arrow-right ml-2 text-xl"></i>
                                    </span>
                                    <span class="mt-0.5 text-base font-extrabold">{{ $nextProfile['name'] }}</span>
                                </button>
                            </div>
                        </a>
                    @endif

                    @if(count($galleryImages) > 0)
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach(array_slice($galleryImages, 0, 2) as $img)
                                <img
                                    src="{{ $img }}"
                                    alt="{{ $profile['name'] }} image"
                                    class="lazy-img gallery-img-clickable aspect-[4/5] w-full cursor-pointer rounded-xl object-cover"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($tours))
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow sm:p-6">
                            <div class="mb-5">
                                <div class="mb-2 flex items-start sm:items-center">
                                    <i class="fa-solid fa-location-dot mr-3 mt-1 text-xl text-pink-500 sm:mt-0 sm:text-2xl"></i>
                                    <span class="text-xl font-extrabold text-pink-600 sm:text-2xl">
                                        Currently touring in {{ $tours[0]['city'] }}
                                    </span>
                                </div>
                                <span class="text-base font-bold text-gray-800 sm:text-lg">
                                    {{ $tours[0]['from'] }} - {{ $tours[0]['to'] }}
                                </span>
                            </div>

                            <a href="#upcoming-tours" class="smooth-scroll block rounded-md border border-pink-300 bg-transparent px-5 py-3 text-center text-base font-medium text-pink-400 transition hover:bg-pink-50 sm:px-6 sm:text-lg">
                                See all my other tours
                            </a>
                        </div>
                    @endif

                    @if(!empty($safeAbout))
                        <div class="mobile-safe-card">
                            <h2 class="mb-2 text-2xl font-semibold text-pink-600">About me</h2>
                            <hr class="mb-4">
                            <div class="profile-content-html text-base leading-relaxed text-gray-900">
                                {!! nl2br($safeAbout) !!}
                            </div>
                        </div>
                    @endif

                    @if(count($galleryImages) > 2)
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                            @foreach(array_slice($galleryImages, 2) as $img)
                                <img
                                    src="{{ $img }}"
                                    alt="{{ $profile['name'] }} image"
                                    class="lazy-img gallery-img-clickable aspect-[4/5] w-full cursor-pointer rounded-xl object-cover"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @endforeach
                        </div>
                    @endif

                    @include('components.gallery-modal')

                    @if(!empty($videos))
                        <section class="mt-6 overflow-hidden">
                            <div class="mb-6">
                                <h2 class="text-2xl font-semibold text-pink-600">Videos</h2>
                                <hr class="mt-2">
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach($videos as $videoUrl)
                                    @php
                                        $videoPath = parse_url($videoUrl, PHP_URL_PATH) ?? '';
                                        $videoExt = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));

                                        if ($videoExt === 'webm') {
                                            $videoMime = 'video/webm';
                                        } elseif ($videoExt === 'ogg' || $videoExt === 'ogv') {
                                            $videoMime = 'video/ogg';
                                        } else {
                                            $videoMime = 'video/mp4';
                                        }
                                    @endphp

                                    <div class="video-card" x-data="videoCard('{{ $videoUrl }}')" x-init="init()">
                                        <div class="video-shell">
                                            <video
                                                x-ref="video"
                                                controls
                                                playsinline
                                                preload="metadata"
                                                class="h-full w-full object-cover"
                                                @loadstart="onLoadStart()"
                                                @loadedmetadata="onReady()"
                                                @loadeddata="onReady()"
                                                @canplay="onReady()"
                                                @canplaythrough="onReady()"
                                                @playing="onPlaying()"
                                                @pause="onPause()"
                                                @waiting="onWaiting()"
                                                @stalled="onWaiting()"
                                                @suspend="onSuspend()"
                                                @ended="onEnded()"
                                                @error="onError()"
                                            >
                                                <source src="{{ $videoUrl }}" type="{{ $videoMime }}">
                                                Your browser does not support the video tag.
                                            </video>

                                            <div class="video-loader" x-show="showLoader" x-transition.opacity x-cloak>
                                                <div class="flex flex-col items-center gap-3">
                                                    <div class="video-loader-spinner"></div>
                                                    <span class="text-sm font-medium text-white">Loading video...</span>
                                                </div>
                                            </div>

                                            <div class="video-play-overlay" x-show="showPlayOverlay" x-transition.opacity x-cloak>
                                                <div class="rounded-full bg-black/50 p-4">
                                                    <i class="fa-solid fa-play text-xl text-white"></i>
                                                </div>
                                            </div>

                                            <div class="video-error" x-show="error" x-transition.opacity x-cloak>
                                                <div class="rounded-xl bg-red-500/90 px-4 py-3 text-white shadow-lg">
                                                    <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                                                    Video unavailable
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if(!empty($tours))
                        <section id="upcoming-tours" class="mt-12 scroll-mt-32">
                            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow sm:p-6">
                                <div class="mb-1 flex items-center">
                                    <i class="fa-solid fa-location-dot mr-2 text-xl text-pink-600"></i>
                                    <h2 class="text-2xl font-semibold text-pink-600">My upcoming tours</h2>
                                </div>
                                <div class="mb-6 border-b border-gray-200"></div>

                                <div class="space-y-4">
                                    @foreach($tours as $tour)
                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center">
                                            <span class="mr-4 text-base font-bold text-pink-600">{{ $tour['city'] }}</span>
                                            <span class="text-base font-semibold text-gray-900">{{ $tour['from'] }} - {{ $tour['to'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endif

                    @if(!empty($profile['profile_message']))
                        <section class="mt-12">
                            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow sm:p-6">
                                <div class="mb-3 flex items-center">
                                    <i class="fa-solid fa-bullhorn mr-2 text-xl text-pink-600"></i>
                                    <h2 class="text-xl font-semibold text-pink-600 sm:text-2xl">
                                        Message from {{ $profile['name'] }}
                                    </h2>
                                </div>
                                <div class="mb-4 border-b border-gray-200"></div>
                                <div class="prose max-w-none break-words text-gray-700 leading-relaxed">
                                    {!! nl2br($profile['profile_message']) !!}
                                </div>
                            </div>
                        </section>
                    @endif

                    @if(!empty($servicesProvided))
                        <section id="contact-me-for" class="mt-12 scroll-mt-32">
                            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow sm:p-6">
                                <div class="mb-1 flex items-center">
                                    <i class="fa-solid fa-comments mr-2 text-xl text-pink-600"></i>
                                    <h2 class="text-2xl font-semibold text-pink-600">Contact me for</h2>
                                </div>
                                <div class="mb-6 w-24 border-b border-pink-300"></div>

                                <ul class="list-none space-y-2 pl-0">
                                    @foreach($servicesProvided as $item)
                                        <li class="flex items-start gap-2 text-base sm:text-lg">
                                            <span class="mt-0.5 text-xl text-pink-600">&raquo;</span>
                                            <span class="text-gray-900">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif

                    <div class="mt-8 mb-2 text-center">
                        <span class="text-base font-medium sm:text-lg" style="background: linear-gradient(90deg, #d77dbb 0%, #6ec1e4 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: transparent;">
                            Find me easily with this short link:
                            <a href="{{ $profileUrl }}" class="break-all hover:underline" style="background: none; color: #4fa3e3;">
                                {{ $profileUrlDisplay }}
                            </a>
                        </span>
                    </div>
                </div>

                <div class="order-2 flex min-w-0 flex-col gap-6 lg:sticky lg:top-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow sm:p-6">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-lg font-bold text-black">Info</span>
                            @if(!empty($profile['is_verified']))
                                <span class="inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">
                                    <i class="fa-solid fa-badge-check mr-1 text-blue-500"></i>
                                    PHOTOS VERIFIED
                                </span>
                            @endif
                        </div>

                        <div class="mb-3 grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                            @if(!empty($profile['age']))
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-hourglass-half w-5 text-center text-pink-600"></i>
                                    <span>Age <span class="ml-1 font-bold text-gray-900">{{ $profile['age'] }}</span></span>
                                </div>
                            @endif

                            @if(!empty($profile['your_length']))
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-ruler-vertical w-5 text-center text-pink-600"></i>
                                    <span>Length <span class="ml-1 font-bold text-gray-900">{{ $profile['your_length'] }}</span></span>
                                </div>
                            @endif

                            @if(!empty($profile['bust_size']))
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-braille w-5 text-center text-pink-600"></i>
                                    <span>Bust Size <span class="ml-1 font-bold text-gray-900">{{ $profile['bust_size'] }}</span></span>
                                </div>
                            @endif

                            @if(!empty($profile['rate']))
                                <div class="col-span-1 flex items-center space-x-2 sm:col-span-2">
                                    <i class="fa-solid fa-dollar-sign w-5 text-center text-pink-600"></i>
                                    <span>Rate <span class="ml-1 font-bold text-gray-900">{{ $profile['rate'] }}</span></span>
                                </div>
                            @endif
                        </div>

                        @if(!empty($primaryPhone) || !empty($profile['website']) || !empty($profile['onlyfans']) || !empty($profile['contact_method']))
                            <div class="mt-4">
                                <span class="mb-1 block text-lg font-bold text-black">Contact</span>

                                <div class="mb-2 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                                    Tell you saw advertisement in <span class="font-semibold text-pink-600">HotEscort</span>, thanks!
                                    @if(!empty($profile['contact_method']))
                                        <br>
                                        Preferred contact method:
                                        <span class="font-semibold">{{ $profile['contact_method'] }}</span>
                                    @endif
                                </div>

                                @if(!empty($primaryPhone))
                                    <div class="mt-2 flex items-center gap-2">
                                        <i class="fa-solid fa-mobile-screen text-2xl text-blue-600"></i>
                                        <span class="text-xs font-bold text-black">PHONE:</span>
                                    </div>
                                    <a href="tel:{{ $phoneHref }}" aria-label="Call {{ $primaryPhone }}" class="mb-2 block break-all text-xl font-bold tracking-wide text-black transition hover:text-pink-600 sm:text-2xl">
                                        {{ $primaryPhone }}
                                    </a>
                                @endif

                                @if(!empty($profile['website']))
                                    <hr class="my-3">
                                    <div class="mt-2 flex items-center gap-2">
                                        <i class="fa-solid fa-globe text-2xl text-blue-600"></i>
                                        <span class="text-xs font-bold text-black">WEBSITE:</span>
                                    </div>
                                    <a href="{{ $profile['website'] }}" class="mb-2 block break-all text-base font-semibold text-pink-600 hover:underline" target="_blank" rel="noopener noreferrer">
                                        {{ $profile['website'] }}
                                    </a>
                                @endif

                                @if(!empty($profile['onlyfans']))
                                    <hr class="my-3">
                                    <div class="mt-2 flex items-center gap-2">
                                        <i class="fas fa-heart text-2xl text-pink-600"></i>
                                        <span class="text-xs font-bold text-black">ONLYFANS:</span>
                                    </div>
                                    <a href="{{ $profile['onlyfans'] }}" class="mb-2 block break-all text-base font-semibold text-pink-600 hover:underline" target="_blank" rel="noopener noreferrer">
                                        {{ $profile['onlyfans'] }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if(!empty($profile['twitter']) || !empty($profile['whatsapp']))
                            <div class="mt-2">
                                <div class="mt-2 flex items-center gap-2">
                                    <i class="fa-solid fa-share-nodes text-2xl text-blue-600"></i>
                                    <span class="text-xs font-bold text-black">SOCIAL MEDIA:</span>
                                </div>

                                <div class="mt-2 flex gap-3">
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

                        <div class="mt-4 flex flex-col gap-4">
                            <button
                                type="button"
                                @click.prevent="toggleFavourite('{{ $profile['slug'] }}')"
                                :class="isFavourite('{{ $profile['slug'] }}') ? 'bg-pink-50 text-pink-700 border-pink-400' : 'bg-white text-pink-700 border-gray-300 hover:bg-pink-50'"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border px-6 py-3 text-base font-semibold transition sm:text-lg"
                                style="border-width:2px;"
                                title="Save favourite"
                            >
                                <i :class="isFavourite('{{ $profile['slug'] }}') ? 'fa-solid fa-heart text-pink-600' : 'fa-regular fa-heart'" class="text-2xl"></i>
                                <span class="font-semibold" x-text="isFavourite('{{ $profile['slug'] }}') ? 'Saved' : 'Save favourite'"></span>
                            </button>
                        </div>
                    </div>

                    @if(!empty($profile['ethnicity']) || !empty($profile['hair_color']) || !empty($profile['hair_length']) || !empty($profile['body_type']) || !empty($profile['age_group']) || !empty($profile['bust_size']) || !empty($profile['your_length']) || !empty($profile['city']) || !empty($profileTags))
                        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow">
                            <h3 class="mb-2 flex items-center gap-2 text-lg font-bold text-pink-600">
                                <i class="fa-solid fa-user-gear text-pink-500"></i> My profile
                            </h3>
                            <hr class="mb-4">

                            <div class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                                @if(!empty($profile['ethnicity']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-globe w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Ethnicity</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['ethnicity'] }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($profile['hair_color']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-palette w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Hair color</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['hair_color'] }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($profile['hair_length']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-scissors w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Hair length</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['hair_length'] }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($profile['body_type']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-child-reaching w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Body type</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['body_type'] }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($profile['age_group']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-hourglass-half w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Age group</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['age_group'] }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($profile['bust_size']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-braille w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Bust size</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['bust_size'] }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($profile['your_length']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-ruler-vertical w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Length</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['your_length'] }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($profile['city']))
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-location-dot w-5 text-center text-pink-600"></i>
                                        <div>
                                            <span>Location</span><br>
                                            <span class="font-bold text-gray-900">{{ $profile['city'] }}{{ !empty($profile['state']) ? ', ' . $profile['state'] : '' }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($profileTags))
                                <div class="mt-6 flex flex-wrap gap-2">
                                    @foreach($profileTags as $tag)
                                        @if(!empty($tag))
                                            <span class="rounded-full bg-pink-600 px-4 py-1 text-sm font-semibold text-white" style="line-height:1.2;">
                                                {{ $tag }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(!empty($nonEmptyRates))
                        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow">
                            <h3 class="mb-2 flex items-center gap-2 text-lg font-bold text-pink-600">
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
                                                $sessionLabel = !empty($rate['description']) ? $rate['description'] : (!empty($rate['group']) ? $rate['group'] : 'Session');
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

                    @if(!empty($nonEmptyAvailability))
                        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow">
                            <h3 class="mb-2 flex items-center gap-2 text-lg font-bold text-pink-600">
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

                    <div
                        x-data="reportModal({
                            reportUrl: '{{ route('profile.report') }}',
                            profileId: {{ $profile['id'] }}
                        })"
                    >
                        <button
                            @click="show()"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-2 font-semibold text-gray-700 transition hover:bg-gray-50"
                            type="button"
                        >
                            <i class="fa-regular fa-flag"></i> Report User
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 px-4"
                        >
                            <div @click.outside="hide()" class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                                <button
                                    @click="hide()"
                                    class="absolute right-4 top-4 text-2xl font-bold leading-none text-gray-400 hover:text-gray-700"
                                    type="button"
                                >
                                    &times;
                                </button>

                                <h2 class="mb-1 flex items-center gap-2 text-xl font-bold text-pink-600">
                                    <i class="fa-regular fa-flag"></i> Report Profile
                                </h2>

                                <p class="mb-4 text-sm text-gray-500">
                                    Help us keep the community safe. All reports are reviewed by our admin team.
                                </p>

                                <div x-show="success" x-cloak class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">
                                    Thank you! Your report has been submitted and will be reviewed by our team.
                                </div>

                                <div x-show="error" x-text="error" x-cloak class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

                                <form x-ref="form" @submit.prevent="submit()">
                                    @csrf
                                    <input type="hidden" name="provider_profile_id" value="{{ $profile['id'] }}">

                                    <div class="mb-3">
                                        <label class="mb-1 block text-sm font-semibold text-gray-700">
                                            Your Name <span class="font-normal text-gray-400">(optional)</span>
                                        </label>
                                        <input type="text" name="reporter_name" placeholder="Enter your name" maxlength="255" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300">
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1 block text-sm font-semibold text-gray-700">
                                            Your Email <span class="font-normal text-gray-400">(optional)</span>
                                        </label>
                                        <input type="email" name="reporter_email" placeholder="Enter your email" maxlength="255" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300">
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1 block text-sm font-semibold text-gray-700">
                                            Reason <span class="text-red-500">*</span>
                                        </label>
                                        <select name="reason" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300">
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
                                        <label class="mb-1 block text-sm font-semibold text-gray-700">
                                            Additional Details <span class="font-normal text-gray-400">(optional)</span>
                                        </label>
                                        <textarea name="description" rows="3" placeholder="Provide any additional details..." maxlength="2000" class="w-full resize-none rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-300"></textarea>
                                    </div>

                                    <div class="flex gap-3">
                                        <button type="button" @click="hide()" class="flex-1 rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                            Cancel
                                        </button>

                                        <button
                                            type="submit"
                                            class="flex-1 rounded-xl bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:opacity-60"
                                            :disabled="submitting"
                                            x-text="submitting ? 'Submitting...' : 'Submit Report'"
                                        ></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section
            x-data="{
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
            x-init="init()"
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
                <div class="group relative">
                    <div class="overflow-hidden px-1 pb-2 sm:px-6">
                        <div class="flex flex-nowrap gap-4 transition-transform duration-500" :style="`transform: translateX(-${page * 100}%);`">
                            @foreach($nearbyProfiles as $nearby)
                                <article class="group relative min-w-full flex-none overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md sm:min-w-[calc(50%-0.5rem)] lg:min-w-[calc(25%-0.75rem)]">
                                    <a href="{{ route('profile.show', array_merge(['slug' => $nearby['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $nearby['name'] }}"></a>

                                    <div class="relative overflow-hidden rounded-t-2xl">
                                        @if(!empty($nearby['image']))
                                            <img src="{{ $nearby['image'] }}" alt="{{ $nearby['name'] }}" class="h-52 w-full origin-center object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async">
                                        @else
                                            <div class="flex h-52 items-center justify-center bg-gray-100 text-gray-400">
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

                                        <h3 class="truncate text-sm font-medium text-gray-800">
                                            {{ $nearby['name'] }}
                                            @if(!empty($nearby['suburb']))
                                                <span class="font-normal text-gray-400">({{ $nearby['suburb'] }})</span>
                                            @endif
                                        </h3>

                                        <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $nearby['rate'] }}</p>

                                        @if(!empty($nearby['in_call']) || !empty($nearby['out_call']))
                                            <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-[11px]">
                                                @if(!empty($nearby['in_call']))
                                                    <span class="inline-flex items-center gap-1 text-gray-600">
                                                        <i class="fa-solid fa-house text-[10px] text-emerald-500"></i>
                                                        <span class="font-medium">In:</span> {{ $nearby['in_call'] }}
                                                    </span>
                                                @endif
                                                @if(!empty($nearby['out_call']))
                                                    <span class="inline-flex items-center gap-1 text-gray-600">
                                                        <i class="fa-solid fa-car text-[10px] text-blue-500"></i>
                                                        <span class="font-medium">Out:</span> {{ $nearby['out_call'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-3 flex flex-wrap items-start gap-x-4 gap-y-1.5 text-[12px] text-gray-600">
                                            @if(!empty($nearby['city']) || !empty($nearby['suburb']))
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-location-dot text-[11px] text-pink-500"></i>
                                                    {{ $nearby['suburb'] ?: $nearby['city'] }}
                                                </span>
                                            @endif

                                            @if(!empty($nearby['service_1']))
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-briefcase text-[11px] text-gray-400"></i>
                                                    {{ $nearby['service_1'] }}
                                                </span>
                                            @endif
                                        </div>

                                        @if(!empty($nearby['service_2']) || !empty($nearby['description']))
                                            <div class="mt-2 line-clamp-2 text-[12px] text-gray-600">
                                                <i class="fa-solid fa-gem mr-1 text-[10px] text-blue-500"></i>
                                                {{ !empty($nearby['service_2']) ? $nearby['service_2'] : $nearby['description'] }}
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="prev()"
                        :disabled="page === 0"
                        class="absolute left-0 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border-2 border-pink-500 bg-white text-pink-600 shadow-lg transition hover:bg-pink-500 hover:text-white sm:h-12 sm:w-12"
                        :class="page === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:scale-110'"
                        title="Previous"
                    >
                        <i class="fa-solid fa-chevron-left text-lg"></i>
                    </button>

                    <button
                        type="button"
                        @click="next()"
                        :disabled="page >= pages - 1"
                        class="absolute right-0 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border-2 border-pink-500 bg-white text-pink-600 shadow-lg transition hover:bg-pink-500 hover:text-white sm:h-12 sm:w-12"
                        :class="page >= pages - 1 ? 'opacity-40 cursor-not-allowed' : 'hover:scale-110'"
                        title="Next"
                    >
                        <i class="fa-solid fa-chevron-right text-lg"></i>
                    </button>
                </div>
            @else
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                            <i class="fa-solid fa-users text-2xl text-gray-400"></i>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-800">No nearby listings found</h3>
                            <p class="mb-4 text-sm text-gray-600">There are currently no other providers in your area.</p>
                            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg bg-pink-500 px-6 py-2 font-medium text-white transition hover:bg-pink-600">
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
<script src="{{ asset('frontend/js/profile-show.js') }}"></script>
@endpush
