@php
    /**
     * Reusable profile card partial.
     *
     * Variables:
     *   $profile           – array from BuildProfileFilterViewData::transformProfile()
     *   $tierBadgeVariant  – optional string: 'home_banner' | 'local_banner' | null (for override badge)
     */
    $tierBadgeVariant = $tierBadgeVariant ?? null;

    $featuredBadgeVariant = null;
    $featuredBadgeLabel = null;
    $featuredBadgeIcon = 'crown';

    if ($tierBadgeVariant === 'home_banner' || !empty($profile['home_banner'])) {
        $featuredBadgeVariant = 'ribbon';
        $featuredBadgeLabel = 'Featured';
    } elseif ($tierBadgeVariant === 'local_banner' || !empty($profile['local_banner'])) {
        $featuredBadgeVariant = 'ribbon';
        $featuredBadgeLabel = 'Local Pick';
        $featuredBadgeIcon = 'star';
    } elseif (!empty($profile['home_featured'])) {
        $featuredBadgeVariant = 'glow';
        $featuredBadgeLabel = 'Top Pick';
        $featuredBadgeIcon = 'star';
    } elseif (!empty($profile['featured'])) {
        $featuredBadgeVariant = 'glow';
        $featuredBadgeLabel = 'Featured';
    }

    $hasFeaturedBadge = $featuredBadgeVariant !== null;
@endphp
<article class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-[0_2px_12px_rgba(0,0,0,0.07)] border border-gray-100 transition-all duration-300 hover:shadow-[0_10px_36px_rgba(0,0,0,0.13)] hover:-translate-y-1">
    {{-- Invisible full-card link --}}
    <a href="{{ $profile['profile_url'] ?? route('profile.show.no-sequence', array_merge(['state' => 'au', 'suburb' => 'australia', 'slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

    {{-- ── Image area ── --}}
    <div class="relative aspect-[3/4] w-full overflow-hidden bg-gray-100">
        @if($profile['image'])
            <img
                src="{{ $profile['image'] }}"
                alt="{{ $profile['name'] }}"
                class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
                decoding="async"
                fetchpriority="low"
            >
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                <i class="fa-solid fa-image text-5xl text-gray-300"></i>
            </div>
        @endif

        {{-- Bottom gradient overlay for text readability --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>

        {{-- Top-left: Online / Available status pills --}}
        <div class="pointer-events-none absolute left-2.5 top-2.5 z-20 flex flex-col items-start gap-1.5">
            @if($profile['active'])
                <span class="inline-flex items-center gap-1.5 rounded-full bg-black/45 px-2.5 py-1 text-[10px] font-semibold leading-none text-white backdrop-blur-sm ring-1 ring-white/15">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400 shadow-[0_0_4px_rgba(52,211,153,0.8)]"></span>
                    Online
                </span>
            @endif
            @if(!empty($profile['available_now']))
                <span class="inline-flex items-center gap-1.5 rounded-full bg-black/45 px-2.5 py-1 text-[10px] font-semibold leading-none text-white backdrop-blur-sm ring-1 ring-white/15">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-fuchsia-400 shadow-[0_0_4px_rgba(232,121,249,0.8)]"></span>
                    Available
                </span>
            @endif
        </div>

        {{-- Top-right: Featured badge --}}
        @if($hasFeaturedBadge)
            <div class="pointer-events-none absolute right-2.5 top-2.5 z-20">
                <x-featured-badge :variant="$featuredBadgeVariant" position="inline" :label="$featuredBadgeLabel" :icon="$featuredBadgeIcon" />
            </div>
        @endif

        {{-- Bottom-right: Photo Verified badge --}}
        @if($profile['verified'])
            <div class="pointer-events-none absolute bottom-2.5 right-2.5 z-20">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-black/45 px-2.5 py-1 text-[10px] font-semibold leading-none text-white backdrop-blur-sm ring-1 ring-cyan-400/40">
                    <i class="fa-solid fa-circle-check text-[10px] text-cyan-400"></i>
                    Photo Verified
                </span>
            </div>
        @endif

        {{-- Bottom-left: Name & Age overlay --}}
        <div class="pointer-events-none absolute bottom-0 left-0 z-20 w-full px-3 pb-3">
            <h3 class="truncate text-[15px] font-bold leading-tight text-white drop-shadow-sm">{{ $profile['name'] }}</h3>
            @if(!empty($profile['age']))
                <p class="mt-0.5 text-[11px] font-medium text-white/75">Age {{ $profile['age'] }}</p>
            @endif
        </div>
    </div>

    {{-- ── Card body ── --}}
    <div class="flex flex-1 flex-col p-3.5">

        {{-- Rate + Favourite --}}
        <div class="flex items-center justify-between gap-2">
            <p class="text-xl font-bold text-gray-900 leading-none">{{ $profile['rate'] }}</p>
            <button
                type="button"
                @click.stop.prevent="toggleFavourite('{{ $profile['id'] }}')"
                :class="isFavourite('{{ $profile['id'] }}') ? 'text-pink-500' : 'text-gray-300 hover:text-pink-400'"
                class="relative z-30 transition-colors"
                title="Favourite"
            >
                <i :class="isFavourite('{{ $profile['id'] }}') ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-base"></i>
            </button>
        </div>

        {{-- Location --}}
        @if($profile['city'] || $profile['suburb'])
            <p class="mt-1.5 flex items-center gap-1 text-[11px] text-gray-500">
                <i class="fa-solid fa-location-dot text-pink-400 text-[10px]" aria-hidden="true"></i>
                {{ $profile['suburb'] ?: $profile['city'] }}
            </p>
        @endif

        {{-- In-call / Out-call --}}
        @if(!empty($profile['in_call']) || !empty($profile['out_call']))
            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-gray-500">
                @if(!empty($profile['in_call']))
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-house text-emerald-500 text-[9px]" aria-hidden="true"></i>
                        <span class="font-medium text-gray-600">In:</span> {{ $profile['in_call'] }}
                    </span>
                @endif
                @if(!empty($profile['out_call']))
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-car text-blue-400 text-[9px]" aria-hidden="true"></i>
                        <span class="font-medium text-gray-600">Out:</span> {{ $profile['out_call'] }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Service tag pills --}}
        @if(!empty($profile['service_1']) || !empty($profile['service_2']))
            <div class="mt-2.5 flex flex-wrap gap-1.5">
                @if(!empty($profile['service_1']))
                    <span class="rounded-full bg-pink-50 px-2.5 py-0.5 text-[10px] font-medium text-pink-600 ring-1 ring-pink-100">
                        {{ $profile['service_1'] }}
                    </span>
                @endif
                @if(!empty($profile['service_2']))
                    <span class="rounded-full bg-purple-50 px-2.5 py-0.5 text-[10px] font-medium text-purple-600 ring-1 ring-purple-100">
                        {{ $profile['service_2'] }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Push footer to bottom --}}
        <div class="flex-1"></div>

        {{-- Footer: View Profile CTA --}}
        <div class="mt-3 border-t border-gray-100 pt-3">
            <span class="flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-pink-500 to-purple-500 px-4 py-2 text-[11px] font-semibold tracking-wide text-white shadow-sm transition-all duration-200 group-hover:from-pink-600 group-hover:to-purple-600 group-hover:shadow-md">
                View Profile
            </span>
        </div>
    </div>
</article>
