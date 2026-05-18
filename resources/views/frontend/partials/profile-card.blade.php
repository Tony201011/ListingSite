@php
    /**
     * Reusable profile card partial.
     *
     * Variables:
     *   $profile           – array from BuildProfileFilterViewData::transformProfile()
     *   $tierBadgeVariant  – optional string: 'home_banner' | 'local_banner' | null (for override badge)
     */
    $tierBadgeVariant = $tierBadgeVariant ?? null;
@endphp
<article
    class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md hover:border-gray-300 hover:-translate-y-0.5"
>
    <a href="{{ route('profile.show', array_merge(['slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

    {{-- Image --}}
    <div class="relative overflow-hidden rounded-t-2xl">
        @if($profile['image'])
            <img
                src="{{ $profile['image'] }}"
                alt="{{ $profile['name'] }}"
                class="w-full object-cover origin-center transition-transform duration-500 group-hover:scale-105 h-52"
                loading="lazy"
                decoding="async"
                fetchpriority="low"
            >
        @else
            <div class="flex items-center justify-center bg-gray-100 text-gray-400 h-52">
                <i class="fa-solid fa-image text-4xl"></i>
            </div>
        @endif

        {{-- Tier badge: Home Banner --}}
        @if($tierBadgeVariant === 'home_banner' || !empty($profile['home_banner']))
            <x-featured-badge variant="ribbon" position="top-right" label="Spotlight" icon="crown" />
        @elseif($tierBadgeVariant === 'local_banner' || !empty($profile['local_banner']))
            <x-featured-badge variant="ribbon" position="top-right" label="Local Pick" icon="star" />
        @elseif(!empty($profile['home_featured']))
            <x-featured-badge variant="glow" position="top-right" label="Top Pick" icon="star" />
        @elseif(!empty($profile['featured']))
            <x-featured-badge variant="glow" position="top-right" />
        @endif

        {{-- Photo Verified / Available Now / Online badges --}}
        <div class="absolute left-0 top-3 z-10 flex flex-col gap-1">
            @if($profile['verified'])
                <span class="inline-flex items-center gap-1 bg-cyan-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0;">
                    <i class="fa-solid fa-camera text-[9px]"></i> Photo Verified
                </span>
            @endif
            @if(!empty($profile['available_now']))
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0; background-color: #e13a8b;">
                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Available Now
                </span>
            @endif
            @if($profile['active'])
                <span class="inline-flex items-center gap-1 bg-emerald-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0;">
                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Online Now
                </span>
            @endif
        </div>
    </div>

    {{-- Content --}}
    <div class="p-3.5">
        {{-- Date + Actions row --}}
        <div class="mb-2 flex items-center justify-between">
            <span class="text-[11px] text-gray-400">{{ $profile['date'] }}</span>
            <div class="flex items-center gap-2 text-gray-400 relative z-20">
                <button
                    type="button"
                    @click.prevent="toggleFavourite('{{ $profile['slug'] }}')"
                    :class="isFavourite('{{ $profile['slug'] }}') ? 'text-pink-500' : 'hover:text-pink-500'"
                    class="transition-colors"
                    title="Favourite"
                >
                    <i :class="isFavourite('{{ $profile['slug'] }}') ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-xs"></i>
                </button>
            </div>
        </div>

        {{-- Name --}}
        <h3 class="text-sm font-medium text-gray-800 truncate">
            {{ $profile['name'] }}@if($profile['suburb']) <span class="text-gray-400 font-normal">({{ $profile['suburb'] }})</span>@endif
        </h3>

        {{-- Rate --}}
        <p class="mt-0.5 text-2xl font-bold text-gray-900">
            {{ $profile['rate'] }}
        </p>

        {{-- In Call / Out Call --}}
        @if(!empty($profile['in_call']) || !empty($profile['out_call']))
            <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-[11px]">
                @if(!empty($profile['in_call']))
                    <span class="inline-flex items-center gap-1 text-gray-600">
                        <i class="fa-solid fa-house text-emerald-500 text-[10px]" aria-hidden="true"></i>
                        <span class="font-medium">In:</span> {{ $profile['in_call'] }}
                    </span>
                @endif
                @if(!empty($profile['out_call']))
                    <span class="inline-flex items-center gap-1 text-gray-600">
                        <i class="fa-solid fa-car text-blue-500 text-[10px]" aria-hidden="true"></i>
                        <span class="font-medium">Out:</span> {{ $profile['out_call'] }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Location + Service --}}
        <div class="mt-3 flex flex-wrap items-start gap-x-4 gap-y-1.5 text-[12px] text-gray-600">
            @if($profile['city'] || $profile['suburb'])
                <span class="inline-flex items-center gap-1">
                    <i class="fa-solid fa-location-dot text-pink-500 text-[11px]"></i>
                    {{ $profile['suburb'] ?: $profile['city'] }}
                </span>
            @endif
            @if(!empty($profile['service_1']))
                <span class="inline-flex items-center gap-1">
                    <i class="fa-solid fa-briefcase text-gray-400 text-[11px]"></i>
                    {{ $profile['service_1'] }}
                </span>
            @endif
        </div>

        {{-- Categories --}}
        @if(!empty($profile['service_2']) || !empty($profile['description']))
            <div class="mt-2 text-[12px] text-gray-600 line-clamp-2">
                <i class="fa-solid fa-gem text-blue-500 text-[10px] mr-1"></i>
                {{ !empty($profile['service_2']) ? $profile['service_2'] : $profile['description'] }}
            </div>
        @endif
    </div>
</article>
