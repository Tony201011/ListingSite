@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="profileSettingPage({
        bookingOpen: @js($errors->any() || session('success') || session('error')),
        photos: @js($profileImages->map(fn ($photo) => [
            'id' => $photo->id,
            'thumbnail_url' => $photo->thumbnail_url ?? '',
            'image_url' => $photo->image_url ?? ($photo->thumbnail_url ?? ''),
            'is_primary' => (bool) $photo->is_primary,
        ])->values()),
        videos: @js($videos->map(fn ($video) => [
            'id' => $video->id,
            'video_url' => $video->video_url ?? '',
            'original_name' => $video->original_name ?? ('Video ' . $video->id),
        ])->values())
    })"
>
    <div class="max-w-6xl mx-auto">
        <button
            type="button"
            onclick="window.history.back()"
            class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium bg-transparent border-0 cursor-pointer"
        >
            <span class="mr-1">&lt;</span> To dashboard
        </button>

        @if ($photoVerification)
            <div class="bg-green-500 text-white rounded-xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm sm:text-base">
                    Your profile is verified. Verified profiles get a verified badge.
                </p>
                <span class="inline-flex items-center px-4 py-2 rounded-lg bg-white/20 text-sm font-semibold">
                    Verified
                </span>
            </div>
        @else
            <div class="bg-red-500 text-white rounded-xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm sm:text-base">
                    Your profile is not yet verified. Verified profiles get a verified badge.
                </p>
                <a
                    href="{{ url('/verify-photo') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-sm font-semibold transition"
                >
                    Verify now
                </a>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
                @php
                    $actions = [
                        ['label' => 'Edit profile', 'url' => route('edit-profile')],
                        ['label' => 'Hide profile', 'url' => url('/hide-profile')],
                        ['label' => 'Photos', 'url' => url('/photos')],
                        ['label' => 'Add photos', 'url' => url('/add-photo')],
                        ['label' => 'My videos', 'url' => url('/my-videos')],
                        ['label' => 'My rates', 'url' => url('/my-rate'), 'isNew' => true],
                        ['label' => 'My tours', 'url' => url('/my-tours')],
                        ['label' => 'Availability', 'url' => url('/my-availability')],
                        ['label' => 'Short URL', 'url' => url('/short-url')],
                        ['label' => 'Online now', 'url' => url('/online-now')],
                        ['label' => 'Available now', 'url' => url('/available-now')],
                        ['label' => 'Set & Forget', 'url' => url('/set-and-forget')],
                        ['label' => 'My Babe Rank', 'url' => url('/my-babe-rank')],
                        ['label' => 'Profile message', 'url' => url('/profile-message')],
                        ['label' => 'Help & FAQ', 'url' => route('faq'), 'isPrimary' => true],
                    ];
                @endphp

                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="inline-flex items-center justify-center px-3 py-2.5 rounded-lg border text-sm font-medium transition {{ !empty($action['isPrimary']) ? 'bg-pink-600 text-white border-pink-600 hover:bg-pink-700' : 'bg-white text-gray-700 border-gray-200 hover:border-pink-300 hover:text-pink-700 hover:bg-pink-50' }}"
                    >
                        {{ $action['label'] }}
                        @if (!empty($action['isNew']))
                            <span class="ml-1 text-[10px] uppercase font-bold text-red-500 align-super">new</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <div class="flex flex-wrap items-end gap-3 mb-1">
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">
                            {{ $userInfo['user']?->name ?? '' }}
                        </h1>
                        <span class="text-gray-500 font-medium">
                            {{ $userInfo['provider_profile']?->suburb ?? ($userInfo['user']?->suburb ?? '') }}
                        </span>
                    </div>

                    <p class="text-pink-600 font-medium mb-6">
                        {{ $userInfo['user']?->name ?? '' }}
                    </p>

                    <section class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">About me</h2>
                        <p class="text-gray-600 leading-relaxed">
                            {{ $userInfo['provider_profile']?->profile_text ?? 'No profile message set yet. Click the "Profile message" button above to add a profile message.' }}
                        </p>
                    </section>

                    <section class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-3">My stats</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                            <div><span class="font-semibold text-pink-700">Age group:</span> {{ $userInfo['age_group_name'] ?? '-' }}</div>
                            <div><span class="font-semibold text-pink-700">Ethnicity:</span> {{ $userInfo['ethnicity_name'] ?? '-' }}</div>
                            <div><span class="font-semibold text-pink-700">Hair color:</span> {{ $userInfo['hair_color_name'] ?? '-' }}</div>
                            <div><span class="font-semibold text-pink-700">Hair length:</span> {{ $userInfo['hair_length_name'] ?? '-' }}</div>
                            <div><span class="font-semibold text-pink-700">Body type:</span> {{ $userInfo['body_type_name'] ?? '-' }}</div>
                            <div><span class="font-semibold text-pink-700">Bust size:</span> {{ $userInfo['bust_size_name'] ?? '-' }}</div>
                            <div><span class="font-semibold text-pink-700">Length:</span> {{ $userInfo['your_length_name'] ?? '-' }}</div>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-4">
                            @php
                                $profile = $userInfo['provider_profile'] ?? null;

                                $tags = collect([
                                    $profile?->primary_identity,
                                    $profile?->attributes,
                                    $profile?->services_style,
                                    $profile?->services_provided,
                                ])
                                ->filter()
                                ->flatMap(function ($item) {
                                    if (is_array($item)) {
                                        return $item;
                                    }

                                    if (is_string($item)) {
                                        $decoded = json_decode($item, true);
                                        return is_array($decoded) ? $decoded : [$item];
                                    }

                                    return [];
                                })
                                ->filter()
                                ->unique()
                                ->values();
                            @endphp

                            @forelse($tags as $tag)
                                <span class="px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">
                                    {{ $tag }}
                                </span>
                            @empty
                                <span class="px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">
                                    No tags added
                                </span>
                            @endforelse
                        </div>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Contact me for</h2>
                        <ul class="space-y-1 text-gray-600">
                            <li>• {{ $profile?->availability ?? '-' }}</li>
                            <li>• {{ $profile?->contact_method ?? '-' }}</li>
                            <li>• {{ $profile?->phone_contact_preference ?? '-' }}</li>
                        </ul>
                    </section>
                </div>

                @if($photoVerification)
                    <div class="rounded-xl border border-pink-200 bg-pink-50 p-4 text-pink-700 font-semibold">
                        Verified profiles get a verified badge. Verified profiles are more likely to be contacted by clients and get more bookings.
                    </div>
                @else
                    <div class="rounded-xl border border-pink-200 bg-pink-50 p-4 text-pink-700 font-semibold">
                        Verification pending
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="text-2xl font-bold text-gray-900 leading-tight">
                                {{ $userInfo['user']?->mobile ?? ($userInfo['user']?->mobile ?? '') }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">I accept phone calls & SMS</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center text-lg">📞</div>
                    </div>

                    <button
                        type="button"
                        @click="bookingOpen = true"
                        class="w-full px-4 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition"
                    >
                        Booking enquiries
                    </button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900">My photos</h3>
                        <span
                            x-show="photos.length"
                            x-transition
                            class="text-xs font-semibold text-pink-700 bg-pink-100 px-2.5 py-1 rounded-full"
                            x-text="photos.length + ' photo' + (photos.length > 1 ? 's' : '')"
                        ></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3" x-show="photos.length">
                        <template x-for="(photo, index) in visiblePhotos" :key="photo.id">
                            <button
                                type="button"
                                @click="openSlider(index)"
                                class="group relative aspect-[3/4] rounded-lg bg-gray-100 border border-gray-200 overflow-hidden text-left focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                                <img
                                    :src="photo.thumbnail_url"
                                    :alt="'Profile Image ' + photo.id"
                                    class="w-full h-full object-cover transition duration-500 ease-out group-hover:scale-110"
                                >

                                <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>

                                <div class="absolute inset-x-0 bottom-0 p-3 translate-y-3 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition duration-300">
                                    <div class="flex items-center justify-between">
                                        <span class="text-white text-xs font-semibold">View photo</span>
                                        <span
                                            x-show="photo.is_primary"
                                            class="text-[10px] font-bold uppercase tracking-wide bg-pink-600 text-white px-2 py-1 rounded-full"
                                        >
                                            Cover
                                        </span>
                                    </div>
                                </div>

                                <div
                                    x-show="index === 1 && remainingPhotoCount > 0"
                                    class="absolute inset-0 bg-black/50 flex items-center justify-center"
                                >
                                    <span
                                        class="text-white text-2xl sm:text-3xl font-bold"
                                        x-text="'+' + remainingPhotoCount"
                                    ></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div x-show="!photos.length" class="grid grid-cols-2 gap-3">
                        <div class="aspect-[3/4] rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-400">
                            Photo 1
                        </div>
                        <div class="aspect-[3/4] rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-400">
                            Photo 2
                        </div>
                    </div>

                    <button
                        type="button"
                        x-show="photos.length > 0"
                        @click="openSlider(0)"
                        class="mt-4 w-full px-4 py-2.5 rounded-lg border border-pink-200 text-pink-700 font-semibold hover:bg-pink-50 transition"
                    >
                        View all photos
                    </button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900">My videos</h3>
                        <span
                            x-show="videos.length"
                            x-transition
                            class="text-xs font-semibold text-pink-700 bg-pink-100 px-2.5 py-1 rounded-full"
                            x-text="videos.length + ' video' + (videos.length > 1 ? 's' : '')"
                        ></span>
                    </div>

                    <div class="grid grid-cols-1 gap-3" x-show="videos.length">
                        <template x-for="(video, index) in visibleVideos" :key="video.id">
                            <button
                                type="button"
                                @click="openVideo(index)"
                                class="group relative aspect-video rounded-lg bg-black border border-gray-200 overflow-hidden text-left focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                                <video
                                    :src="video.video_url"
                                    class="w-full h-full object-cover"
                                    muted
                                    preload="metadata"
                                ></video>

                                <div class="absolute inset-0 bg-black/35 group-hover:bg-black/50 transition duration-300"></div>

                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center shadow-lg group-hover:scale-110 transition duration-300">
                                        <svg class="w-6 h-6 text-pink-600 ml-1" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5v14l11-7z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <p
                                        class="text-white text-xs font-semibold truncate"
                                        x-text="video.original_name"
                                    ></p>
                                </div>

                                <div
                                    x-show="index === 1 && remainingVideoCount > 0"
                                    class="absolute inset-0 bg-black/55 flex items-center justify-center"
                                >
                                    <span
                                        class="text-white text-2xl sm:text-3xl font-bold"
                                        x-text="'+' + remainingVideoCount"
                                    ></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div x-show="!videos.length" class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-400">
                        No videos uploaded yet
                    </div>

                    <button
                        type="button"
                        x-show="videos.length > 0"
                        @click="openVideo(0)"
                        class="mt-4 w-full px-4 py-2.5 rounded-lg border border-pink-200 text-pink-700 font-semibold hover:bg-pink-50 transition"
                    >
                        View all videos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Enquiry Modal -->
    <div
        x-show="bookingOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
        @click.self="bookingOpen = false"
    >
        <div
            x-show="bookingOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="w-full max-w-xl bg-white rounded-2xl shadow-2xl p-5 sm:p-6 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Email booking enquiry</h2>
                <button
                    type="button"
                    @click="bookingOpen = false"
                    class="text-gray-500 hover:text-gray-700 text-2xl leading-none"
                >
                    &times;
                </button>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold mb-2">Please fix the following errors:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('booking.enquiry') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Your email (required)" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Your phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="datetime-local" name="datetime" value="{{ old('datetime') }}" min="{{ now()->format('Y-m-d\TH:i') }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="text" name="services" value="{{ old('services') }}" placeholder="What services are you interested in" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="text" name="duration" value="{{ old('duration') }}" placeholder="How long would you like to book" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="text" name="location" value="{{ old('location') }}" placeholder="Where would you like to meet" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <textarea name="message" rows="3" maxlength="2000" placeholder="Any other comments" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">{{ old('message') }}</textarea>

                <div class="pt-2 flex gap-3">
                    <button
                        type="submit"
                        class="flex-1 px-4 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition"
                    >
                        Submit
                    </button>

                    <button
                        type="button"
                        @click="bookingOpen = false"
                        class="px-4 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Photo Slider / Lightbox -->
    <div
        x-show="sliderOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4"
        @click.self="closeSlider()"
        @keydown.escape.window="closeSlider()"
        @keydown.left.window="prevSlide()"
        @keydown.right.window="nextSlide()"
    >
        <button
            type="button"
            @click="closeSlider()"
            class="absolute top-4 right-4 text-white/80 hover:text-white text-4xl leading-none z-10 transition"
        >
            &times;
        </button>

        <button
            type="button"
            @click="prevSlide()"
            class="absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white text-5xl leading-none z-10 transition"
            :class="{ 'opacity-40 cursor-not-allowed': photos.length <= 1 }"
        >
            &lsaquo;
        </button>

        <button
            type="button"
            @click="nextSlide()"
            class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white text-5xl leading-none z-10 transition"
            :class="{ 'opacity-40 cursor-not-allowed': photos.length <= 1 }"
        >
            &rsaquo;
        </button>

        <template x-if="photos.length > 0 && photos[sliderIndex]">
            <div
                x-show="sliderOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                class="max-w-5xl w-full flex flex-col items-center"
            >
                <div class="relative overflow-hidden rounded-2xl">
                    <img
                        :src="photos[sliderIndex].image_url"
                        :alt="'Photo ' + photos[sliderIndex].id"
                        class="max-h-[85vh] max-w-full object-contain rounded-2xl shadow-2xl"
                    >
                </div>

                <div class="mt-4 text-white text-sm sm:text-base font-medium flex items-center gap-2">
                    <span x-text="'Photo #' + photos[sliderIndex].id"></span>
                    <span
                        x-show="photos[sliderIndex].is_primary"
                        class="px-2 py-1 rounded-full text-xs bg-pink-600 text-white"
                    >
                        Cover photo
                    </span>
                </div>

                <div
                    x-show="photos.length > 1"
                    class="mt-4 flex items-center gap-2 flex-wrap justify-center"
                >
                    <template x-for="(photo, index) in photos" :key="'dot-' + photo.id">
                        <button
                            type="button"
                            @click="sliderIndex = index"
                            class="h-2.5 rounded-full transition-all duration-300"
                            :class="sliderIndex === index ? 'w-8 bg-pink-500' : 'w-2.5 bg-white/50 hover:bg-white/80'"
                        ></button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Video Modal -->
    <div
        x-show="videoOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[70] bg-black/90 flex items-center justify-center p-4"
        @click.self="closeVideo()"
        @keydown.escape.window="closeVideo()"
        @keydown.left.window="prevVideo()"
        @keydown.right.window="nextVideo()"
    >
        <button
            type="button"
            @click="closeVideo()"
            class="absolute top-4 right-4 text-white/80 hover:text-white text-4xl leading-none z-10 transition"
        >
            &times;
        </button>

        <button
            type="button"
            @click="prevVideo()"
            class="absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white text-5xl leading-none z-10 transition"
            :class="{ 'opacity-40 cursor-not-allowed': videos.length <= 1 }"
        >
            &lsaquo;
        </button>

        <button
            type="button"
            @click="nextVideo()"
            class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white text-5xl leading-none z-10 transition"
            :class="{ 'opacity-40 cursor-not-allowed': videos.length <= 1 }"
        >
            &rsaquo;
        </button>

        <template x-if="videos.length > 0 && videos[videoIndex]">
            <div
                x-show="videoOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                class="max-w-5xl w-full flex flex-col items-center"
            >
                <div class="relative overflow-hidden rounded-2xl w-full">
                    <video
                        :src="videos[videoIndex].video_url"
                        class="max-h-[85vh] w-full rounded-2xl shadow-2xl bg-black"
                        controls
                        autoplay
                        playsinline
                    ></video>
                </div>

                <div class="mt-4 text-white text-sm sm:text-base font-medium">
                    <span x-text="videos[videoIndex].original_name"></span>
                </div>

                <div
                    x-show="videos.length > 1"
                    class="mt-4 flex items-center gap-2 flex-wrap justify-center"
                >
                    <template x-for="(video, index) in videos" :key="'video-dot-' + video.id">
                        <button
                            type="button"
                            @click="videoIndex = index"
                            class="h-2.5 rounded-full transition-all duration-300"
                            :class="videoIndex === index ? 'w-8 bg-pink-500' : 'w-2.5 bg-white/50 hover:bg-white/80'"
                        ></button>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/profile-setting.js') }}"></script>
@endpush
