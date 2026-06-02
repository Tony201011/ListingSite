@extends('layouts.frontend')

@section('title', 'My Listings')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Page Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">My Listings</h1>
                <p class="mt-2 max-w-xl text-sm text-gray-500">
                    Manage all of your listings in one place, renew expiring listings, and purchase premium features.
                </p>
            </div>
            <div class="flex flex-shrink-0 flex-wrap items-center gap-3">
                <a href="/" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i>
                    View Listings
                </a>
                <a href="{{ route('my-profile') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fa-solid fa-plus"></i>
                    Post New Advertisement
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-6 py-4 text-sm font-medium text-green-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter + Search Bar --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">

                {{-- Status filter tabs --}}
                <div class="flex flex-wrap items-center gap-2">
                    @foreach(['all' => 'All Listings', 'online' => 'Online', 'expiring' => 'Expiring', 'expired' => 'Expired', 'offline' => 'Offline'] as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition
                               {{ $status === $key
                                   ? 'bg-blue-600 text-white shadow-sm'
                                   : 'text-gray-600 hover:bg-gray-100' }}">
                            {{ $label }}
                            <span class="rounded-md px-1.5 py-0.5 text-[11px] font-semibold
                                {{ $status === $key ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                                {{ $statusCounts[$key] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>

                {{-- Search & Sort --}}
                <form method="GET" action="{{ route('my-listings') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input name="q" value="{{ $searchQuery ?? '' }}" type="search" placeholder="Search listings…"
                               class="w-56 rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-9 pr-4 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="sr-only" for="sort">Sort</label>
                        <select id="sort" name="sort"
                                class="rounded-xl border border-gray-200 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="oldest" {{ ($sort ?? 'oldest') === 'oldest' ? 'selected' : '' }}>Upload Date · Oldest First</option>
                            <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Upload Date · Newest First</option>
                        </select>
                        <button type="submit"
                                class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Listing Cards Grid --}}
        @if($listings->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($listings as $listing)
                @php
                    $isOnline  = $listing->is_live && $listing->is_active;
                    $isExpired = !$listing->is_active;
                    $isExpiring = $listing->is_active && !$listing->is_live && $listing->created_at->lte(now()->subDays(7));

                    if ($isOnline) {
                        $badgeClass = 'bg-green-500 text-white';
                        $badgeLabel = 'Online';
                    } elseif ($isExpired) {
                        $badgeClass = 'bg-red-500 text-white';
                        $badgeLabel = 'Expired';
                    } elseif ($isExpiring) {
                        $badgeClass = 'bg-amber-500 text-white';
                        $badgeLabel = 'Expiring';
                    } else {
                        $badgeClass = 'bg-gray-600 text-white';
                        $badgeLabel = 'Offline';
                    }

                    $location = $listing->providerProfile?->suburb ?? null;
                @endphp
                <article class="flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">

                    {{-- Card image with overlays --}}
                    <div class="relative aspect-[16/10] overflow-hidden bg-gray-100">
                        @if($listing->thumbnail)
                            <img src="{{ url($listing->thumbnail) }}"
                                 alt="{{ $listing->title }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center bg-gray-200">
                                <i class="fa-regular fa-image text-4xl text-gray-400"></i>
                            </div>
                        @endif

                        {{-- Status badge top-left --}}
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold shadow {{ $badgeClass }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-white/70"></span>
                            {{ $badgeLabel }}
                        </span>

                        {{-- Three-dot action menu top-right --}}
                        <div class="absolute right-3 top-3" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-gray-600 shadow transition hover:bg-white">
                                <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                            </button>
                            <div x-show="open" x-transition
                                 class="absolute right-0 top-10 z-10 w-44 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg">
                                <a href="{{ route('my-listings.show', $listing) }}"
                                   class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fa-regular fa-eye w-4 text-gray-400"></i> View Details
                                </a>
                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="feature" value="top">
                                    <button type="submit"
                                            class="flex w-full items-center gap-2 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-arrow-up w-4 text-gray-400"></i> Mark Online
                                    </button>
                                </form>
                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="feature" value="premium">
                                    <button type="submit"
                                            class="flex w-full items-center gap-2 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-crown w-4 text-gray-400"></i> Upgrade Premium
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Card body --}}
                    <div class="flex flex-1 flex-col p-5">

                        {{-- Listing info --}}
                        <div class="mb-4">
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="line-clamp-1 text-base font-semibold text-gray-900">{{ $listing->title }}</h2>
                                @if($listing->is_vip)
                                    <span class="inline-flex shrink-0 items-center rounded-md bg-violet-50 px-2 py-0.5 text-[11px] font-semibold text-violet-700">Premium</span>
                                @endif
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                @if($location)
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-gray-400"></i>
                                        {{ $location }}
                                    </span>
                                    <span class="text-gray-300">·</span>
                                @endif
                                <span class="inline-flex items-center gap-1">
                                    <i class="fa-solid fa-tag text-gray-400"></i>
                                    {{ $listing->category ?: 'Uncategorized' }}
                                </span>
                                <span class="text-gray-300">·</span>
                                <span class="inline-flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-gray-400"></i>
                                    Updated {{ $listing->updated_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Stats row --}}
                        <div class="mb-4 grid grid-cols-4 divide-x divide-gray-100 overflow-hidden rounded-xl border border-gray-100 bg-gray-50 text-center">
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Views</p>
                            </div>
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Likes</p>
                            </div>
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Contacts</p>
                            </div>
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Leads</p>
                            </div>
                        </div>

                        {{-- Promotion section --}}
                        <div class="mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-sm font-semibold text-gray-800">Get More Views</p>
                            <p class="mt-0.5 text-xs text-gray-500">Promote your listing and improve visibility.</p>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="feature" value="top">
                                    <button type="submit"
                                            class="w-full rounded-lg border border-yellow-300 bg-white px-2 py-2 text-xs font-semibold text-yellow-700 transition hover:bg-yellow-50">
                                        <i class="fa-solid fa-arrow-up mb-0.5 block text-yellow-500"></i>
                                        Top
                                    </button>
                                </form>
                                <a href="{{ route('photos') }}"
                                   class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white px-2 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                                    <i class="fa-regular fa-images mb-0.5 text-gray-400"></i>
                                    Gallery
                                </a>
                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="feature" value="premium">
                                    <button type="submit"
                                            class="w-full rounded-lg border border-violet-300 bg-white px-2 py-2 text-xs font-semibold text-violet-700 transition hover:bg-violet-50">
                                        <i class="fa-solid fa-crown mb-0.5 block text-violet-500"></i>
                                        Premium
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Details button --}}
                        <div class="mt-auto flex justify-end">
                            <a href="{{ route('my-listings.show', $listing) }}"
                               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                Details
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

        {{-- Fallback: show provider profiles when no ProviderListings exist --}}
        @elseif(isset($profiles) && $profiles->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($profiles as $profile)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">

                    {{-- Card image --}}
                    <div class="relative aspect-[16/10] overflow-hidden bg-gray-100">
                        @if($profile->primaryProfileImage)
                            <img src="{{ $profile->primaryProfileImage->thumbnail_url }}"
                                 alt="{{ $profile->name }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center bg-gray-200">
                                <i class="fa-regular fa-image text-4xl text-gray-400"></i>
                            </div>
                        @endif

                        <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold shadow
                            {{ $profile->isCurrentlyOnline() ? 'bg-green-500 text-white' : 'bg-gray-600 text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-white/70"></span>
                            {{ $profile->isCurrentlyOnline() ? 'Online' : 'Offline' }}
                        </span>
                    </div>

                    {{-- Card body --}}
                    <div class="flex flex-1 flex-col p-5">
                        <div class="mb-4">
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="line-clamp-1 text-base font-semibold text-gray-900">{{ $profile->name }}</h2>
                                @if($profile->is_featured)
                                    <span class="inline-flex shrink-0 items-center rounded-md bg-violet-50 px-2 py-0.5 text-[11px] font-semibold text-violet-700">VIP</span>
                                @endif
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                @if($profile->suburb)
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-gray-400"></i>
                                        {{ $profile->suburb }}
                                    </span>
                                    <span class="text-gray-300">·</span>
                                @endif
                                <span>{{ $profile->age ? $profile->age.' yrs' : 'Age not set' }}</span>
                            </div>
                        </div>

                        {{-- Stats row --}}
                        <div class="mb-4 grid grid-cols-4 divide-x divide-gray-100 overflow-hidden rounded-xl border border-gray-100 bg-gray-50 text-center">
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Views</p>
                            </div>
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Likes</p>
                            </div>
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Contacts</p>
                            </div>
                            <div class="px-2 py-2.5">
                                <p class="text-base font-bold text-gray-800">0</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-400">Leads</p>
                            </div>
                        </div>

                        {{-- Promotion section --}}
                        <div class="mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-sm font-semibold text-gray-800">Get More Views</p>
                            <p class="mt-0.5 text-xs text-gray-500">Promote your listing and improve visibility.</p>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <a href="{{ route('profiles.switch', $profile) }}"
                                   class="flex flex-col items-center justify-center rounded-lg border border-yellow-300 bg-white px-2 py-2 text-xs font-semibold text-yellow-700 transition hover:bg-yellow-50">
                                    <i class="fa-solid fa-arrow-up mb-0.5 text-yellow-500"></i>
                                    Top
                                </a>
                                <a href="{{ route('photos') }}"
                                   class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white px-2 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                                    <i class="fa-regular fa-images mb-0.5 text-gray-400"></i>
                                    Gallery
                                </a>
                                <a href="{{ route('featured') }}"
                                   class="flex flex-col items-center justify-center rounded-lg border border-violet-300 bg-white px-2 py-2 text-xs font-semibold text-violet-700 transition hover:bg-violet-50">
                                    <i class="fa-solid fa-crown mb-0.5 text-violet-500"></i>
                                    Premium
                                </a>
                            </div>
                        </div>

                        {{-- Details button --}}
                        <div class="mt-auto flex justify-end">
                            <a href="{{ route('profiles.switch', $profile) }}"
                               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                Details
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

        @else
            {{-- Empty state --}}
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-16 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100">
                    <i class="fa-regular fa-rectangle-list text-2xl text-gray-400"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">No listings yet</h2>
                <p class="mt-2 text-sm text-gray-500">You don't have any listings yet. Head to the dashboard to create your first one.</p>
                <a href="{{ route('my-profile') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fa-solid fa-plus"></i>
                    Go to Dashboard
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
