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

            @php
                $selectedCategoryIds = collect($selectedCategoryIds ?? [])->map(fn ($id) => (string) $id)->values();
            @endphp

            <form method="GET" action="{{ url('/') }}" class="mt-6 space-y-4 text-sm text-gray-600" x-data="{ minAge: {{ (int) ($minAge ?? 18) }}, maxAge: {{ (int) ($maxAge ?? 40) }}, minPrice: {{ (int) ($minPrice ?? 150) }}, maxPrice: {{ (int) ($maxPrice ?? 400) }} }">
                <div>
                    <label for="location" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Location</label>
                    <input
                        id="location"
                        name="location"
                        type="text"
                        value="{{ request('location') }}"
                        placeholder="Type location & select result from list"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-pink-400 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Age Range</label>
                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                        <div class="mb-2 flex items-center justify-between text-xs text-gray-500">
                            <span>Min: <strong x-text="minAge"></strong></span>
                            <span>Max: <strong x-text="maxAge"></strong></span>
                        </div>
                        <input id="min-age" name="min_age" type="range" min="18" max="60" x-model.number="minAge" @input="if (minAge > maxAge) maxAge = minAge" class="w-full">
                        <input id="max-age" name="max_age" type="range" min="18" max="60" x-model.number="maxAge" @input="if (maxAge < minAge) minAge = maxAge" class="mt-2 w-full">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Price Range</label>
                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                        <div class="mb-2 flex items-center justify-between text-xs text-gray-500">
                            <span>Min: $<strong x-text="minPrice"></strong></span>
                            <span>Max: $<strong x-text="maxPrice"></strong></span>
                        </div>
                        <input id="min-price" name="min_price" type="range" min="100" max="1000" step="10" x-model.number="minPrice" @input="if (minPrice > maxPrice) maxPrice = minPrice" class="w-full">
                        <input id="max-price" name="max_price" type="range" min="100" max="1000" step="10" x-model.number="maxPrice" @input="if (maxPrice < minPrice) minPrice = maxPrice" class="mt-2 w-full">
                    </div>
                </div>

                @forelse(($filterGroups ?? []) as $group)
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">{{ $group['label'] }}</label>
                        @if(!empty($group['options']))
                            @php
                                $groupSelectedIds = collect($group['options'])
                                    ->pluck('id')
                                    ->map(fn ($id) => (string) $id)
                                    ->intersect($selectedCategoryIds)
                                    ->values()
                                    ->all();
                            @endphp
                            <div
                                class="space-y-2"
                                x-data="{ open: false, options: {{ \Illuminate\Support\Js::from($group['options']) }}, selected: {{ \Illuminate\Support\Js::from($groupSelectedIds) }} }"
                                @click.outside="open = false"
                                @filter-opened.window="if ($event.detail !== '{{ $group['slug'] }}') open = false"
                            >
                                <template x-for="id in selected" :key="'sel-' + id">
                                    <input type="hidden" name="categories[]" :value="id">
                                </template>

                                <button
                                    type="button"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-sm font-semibold text-gray-700"
                                    @click="open = !open; if (open) $dispatch('filter-opened', '{{ $group['slug'] }}')"
                                >
                                    <span class="text-gray-500" x-show="selected.length === 0">Please select...</span>
                                    <span class="inline-flex flex-wrap items-center gap-1" x-show="selected.length > 0">
                                        <template x-for="id in selected" :key="'chip-' + id">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                                <span x-text="(options.find(o => String(o.id) === String(id)) || {}).name"></span>
                                                <button type="button" class="text-gray-500" @click.stop="selected = selected.filter(v => String(v) !== String(id))">×</button>
                                            </span>
                                        </template>
                                    </span>
                                </button>

                                <div x-cloak x-show="open" x-transition class="max-h-40 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-white p-3">
                                    <template x-for="option in options.filter(o => !selected.includes(String(o.id)))" :key="'opt-' + option.id">
                                        <button type="button" class="block w-full rounded px-2 py-1 text-left text-sm text-gray-700 hover:bg-gray-100" @click="selected = [...selected, String(option.id)]" x-text="option.name"></button>
                                    </template>
                                    <p class="text-xs text-gray-400" x-show="options.filter(o => !selected.includes(String(o.id))).length === 0">No options left.</p>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">No options.</span>
                        @endif
                    </div>
                @empty
                    <span class="text-xs text-gray-400">No filters found.</span>
                @endforelse

                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center">
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">Apply Filters</button>
                    <a href="{{ route('advanced-search') }}" class="inline-flex items-center justify-center rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
