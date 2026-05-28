@extends('layouts.frontend')

@section('title', 'My Favourites')

@section('content')
<div class="min-h-screen bg-gray-100 text-gray-800">

    {{-- Page Header --}}
    <div class="bg-gray-950 border-b border-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-heart text-pink-500 text-xl"></i>
                <div>
                    <h1 class="text-2xl font-bold text-white">My Favourites</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Listings you've saved as favourites</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        x-data="favouriteBookmark({
            favourites: {{ Js::from($userFavourites ?? []) }}
        })"
    >

        {{-- Profile Cards Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5" x-show="favourites.length > 0 && {{ count($profiles) > 0 ? 'true' : 'false' }}">
            @forelse($profiles as $profile)
                <div x-cloak x-show="isFavourite('{{ $profile['id'] }}')">
                    @include('frontend.partials.profile-card', ['profile' => $profile])
                </div>
            @empty
                {{-- empty state shown by default --}}
            @endforelse
        </div>

        {{-- Empty state (shown when no favourites, or all were removed) --}}
        <div x-show="{{ empty($profiles) ? 'true' : 'favourites.length === 0' }}" class="rounded-2xl border border-dashed border-gray-300 bg-white p-16 text-center">
            <i class="fa-regular fa-heart mb-4 text-4xl text-gray-300"></i>
            <p class="text-base font-medium text-gray-600">You haven't saved any favourites yet.</p>
            <p class="mt-1 text-sm text-gray-500">Browse listings and click the <i class="fa-regular fa-heart text-pink-400"></i> icon to save them here.</p>
            <a href="{{ url('/') }}" class="mt-5 inline-block rounded-lg bg-pink-600 px-5 py-2 text-sm font-semibold text-white hover:bg-pink-700 transition">Browse Listings</a>
        </div>

    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('frontend/js/home.js') }}"></script>
@endpush
