@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="hideToggle({
        initialStatus: @js((bool) $status),
        updateUrl: @js(route('update-hide-show-profile')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="mx-auto max-w-3xl">
        @include('profile.partials.back-to-settings')

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="mb-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                Hide profile
            </h1>

            <p class="mb-6 text-gray-600">
                Temporarily hide your profile from public listings and re-enable anytime.
            </p>

            <div
                class="mb-6 rounded-xl border p-4"
                :class="enabled ? 'border-pink-200 bg-pink-50' : 'border-gray-200 bg-gray-50'"
            >
                <p
                    class="font-semibold"
                    :class="enabled ? 'text-pink-700' : 'text-gray-700'"
                    x-text="enabled ? 'Profile is hidden' : 'Profile is visible'"
                ></p>
            </div>

            <button
                type="button"
                @click="toggleStatus()"
                :disabled="loading"
                class="flex items-center justify-center gap-2 rounded-lg px-6 py-2.5 font-semibold transition"
                :class="enabled
                    ? 'bg-pink-600 text-white hover:bg-pink-700'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50'"
            >
                <span
                    x-show="!loading"
                    x-cloak
                    x-text="enabled ? 'Show profile' : 'Hide profile'"
                ></span>

                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <svg
                        class="h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    Updating...
                </span>
            </button>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('profile/js/hide-toggle.js') }}?v={{ filemtime(public_path('profile/js/hide-toggle.js')) }}"></script>
@endpush
