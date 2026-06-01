@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="availableToggle({
        initialStatus: @js((bool) $status),
        initialStartedAt: @js($startedAt ?? null),
        initialBlockedBalance: @js((bool) $blockedBalance),
        updateUrl: @js(route('available.update-status'))
    })"
>
    <div class="mx-auto max-w-3xl">
        @include('profile.partials.back-to-settings')

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="mb-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                Available Now
            </h1>

            <p class="mb-2 text-gray-600">
                Mark yourself available for immediate enquiries and improve visibility.
            </p>

            <div class="mb-6">
                <span
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold"
                    :class="enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                >
                    <span class="h-2.5 w-2.5 rounded-full" :class="enabled ? 'bg-green-500' : 'bg-gray-400'"></span>
                    <span x-text="enabled ? 'Available Now is enabled' : 'Available Now is disabled'"></span>
                </span>
            </div>

            <button
                type="button"
                @click="toggleStatus()"
                :disabled="loading || (!enabled && blockedBalance)"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 font-semibold transition sm:w-auto"
                :class="enabled
                    ? 'bg-pink-600 text-white hover:bg-pink-700'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50'"
            >
                <span
                    x-show="!loading"
                    x-cloak
                    x-text="enabled ? 'Disable available now' : 'Enable available now'"
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

            <template x-if="message">
                <div
                    class="mt-4 text-sm"
                    :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'"
                    x-text="message"
                ></div>
            </template>

            <div
                x-show="!enabled && blockedBalance"
                x-cloak
                x-transition
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"
            >
                Your 21-day period has expired and your account balance is negative. Please clear your balance to go online or become available now.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('profile/js/available-toggle.js') }}?v={{ filemtime(public_path('profile/js/available-toggle.js')) }}"></script>
@endpush
