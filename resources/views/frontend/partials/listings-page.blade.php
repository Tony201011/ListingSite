{{--
    Minimal partial returned for infinite-scroll pagination requests (X-Listing-Page header).
    The home.js loadMore function parses the response DOM and extracts
    [data-listings-grid] and [data-listings-pagination] from inside #listings-content.
--}}
<div id="listings-content">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-listings-grid>
        @foreach($profiles as $profile)
            @include('frontend.partials.profile-card', ['profile' => $profile])
        @endforeach
    </div>

    <div class="mt-10" data-listings-pagination>
        {{ $profiles->onEachSide(1)->links('vendor.pagination.home') }}
    </div>
    <div class="mt-4 hidden text-center text-sm text-gray-500" data-listings-loading>
        Loading more profiles...
    </div>
    <div class="mt-4 hidden text-center text-sm text-red-500" data-listings-error>
        Something went wrong while loading more profiles.
    </div>
    <div class="mt-4 hidden text-center text-sm text-gray-400" data-listings-end>
        No more profiles to load.
    </div>
    <div class="h-2 w-full" data-listings-sentinel @if(! $profiles->hasMorePages()) hidden @endif></div>
</div>
