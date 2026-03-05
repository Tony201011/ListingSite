@extends('layouts.frontend')

@section('title', 'Advanced Search / Filter')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-800">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-700">Home</a>
            <span>›</span>
            <span class="text-gray-700">Advanced Search / Filter</span>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <h1 class="text-xl font-bold text-gray-900">Advanced Search / Filter</h1>
            <p class="mt-1 text-sm text-gray-500">Use filters and show matching profiles on home page.</p>

            <form method="GET" action="{{ url('/') }}" class="mt-6 space-y-5">
                <div>
                    <label for="location" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Location</label>
                    <input
                        id="location"
                        name="location"
                        type="text"
                        value="{{ request('location') }}"
                        placeholder="Enter city or area"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 placeholder:text-gray-400 focus:border-pink-400 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="escort_name" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Escort Name</label>
                    <input
                        id="escort_name"
                        name="escort_name"
                        type="text"
                        value="{{ request('escort_name') }}"
                        placeholder="Optional name search"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 placeholder:text-gray-400 focus:border-pink-400 focus:outline-none"
                    >
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="min_age" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Min Age</label>
                        <input id="min_age" name="min_age" type="number" min="18" max="60" value="{{ $minAge }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 focus:border-pink-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="max_age" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Max Age</label>
                        <input id="max_age" name="max_age" type="number" min="18" max="60" value="{{ $maxAge }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 focus:border-pink-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="min_price" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Min Price</label>
                        <input id="min_price" name="min_price" type="number" min="100" max="1000" step="10" value="{{ $minPrice }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 focus:border-pink-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="max_price" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Max Price</label>
                        <input id="max_price" name="max_price" type="number" min="100" max="1000" step="10" value="{{ $maxPrice }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 focus:border-pink-400 focus:outline-none">
                    </div>
                </div>

                @forelse(($filterGroups ?? []) as $group)
                    <div>
                        <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-700">{{ $group['label'] }}</h3>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach(($group['options'] ?? []) as $option)
                                <label class="inline-flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                    <input
                                        type="checkbox"
                                        name="categories[]"
                                        value="{{ $option['id'] }}"
                                        @checked(collect($selectedCategoryIds ?? [])->contains((int) $option['id']))
                                        class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                    >
                                    <span>{{ $option['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No filter groups found.</p>
                @endforelse

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">Search</button>
                    <a href="{{ route('advanced-search') }}" class="rounded-md border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
