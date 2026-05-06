{{--
    Ad slot partial.

    Usage:
        @include('layouts.partials.ads', ['position' => 'home_top'])
        @include('layouts.partials.ads', ['position' => 'home_between', 'pageKey' => 'home'])

    Parameters:
        $position  – one of the position slugs defined in AdResource::positionOptions()
        $pageKey   – optional page key; defaults to the current request path
--}}
@php
    $pageKey = $pageKey ?? (trim(request()->path(), '/') ?: 'home');
    $ads = \App\Models\Ad::forPositionAndPage($position, $pageKey);
@endphp

@if($ads->isNotEmpty())
    <div class="w-full my-4 flex flex-col gap-3" aria-label="Advertisement">
        @foreach($ads as $ad)
            @if($ad->link_url)
                <a
                    href="{{ $ad->link_url }}"
                    {{ $ad->open_in_new_tab ? 'target="_blank" rel="noopener noreferrer"' : '' }}
                    class="block"
                    @if($ad->title) title="{{ $ad->title }}" @endif
                >
                    <img
                        src="{{ $ad->image_url }}"
                        alt="{{ $ad->title ?? 'Advertisement' }}"
                        class="w-full rounded-lg object-cover"
                        loading="lazy"
                        decoding="async"
                    >
                </a>
            @else
                <div class="block">
                    <img
                        src="{{ $ad->image_url }}"
                        alt="{{ $ad->title ?? 'Advertisement' }}"
                        class="w-full rounded-lg object-cover"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
            @endif
        @endforeach
    </div>
@endif
