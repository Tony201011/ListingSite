@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="shortUrlForm({
        initialSlug: @js($slug),
        baseUrl: @js(config('app.url')),
        updateUrl: @js(route('short-url.update')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="max-w-3xl mx-auto">
        @include('profile.partials.back-to-settings')

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Short URL</h1>
            <p class="text-gray-600 mb-4">Set a clean URL that is easy to share.</p>

            <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden bg-white">
                <span class="px-3 py-2.5 bg-gray-50 text-gray-500 text-sm border-r">
                    {{ config('app.url') }}/
                </span>
                <input
                    type="text"
                    x-model="slug"
                    @input="clearMessages"
                    class="flex-1 px-3 py-2.5 focus:outline-none"
                    placeholder="your-custom-slug"
                >
            </div>

            <div class="mt-3 text-sm text-gray-500">
                Preview:
                <span class="text-pink-600" x-text="fullUrl"></span>
            </div>

            <button
                @click="saveSlug"
                :disabled="saving"
                class="mt-4 px-5 py-2.5 rounded-lg bg-pink-600 text-white"
            >
                <span x-show="!saving">Save URL</span>
                <span x-show="saving">Saving...</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/short-url.js') }}"></script>
@endpush
