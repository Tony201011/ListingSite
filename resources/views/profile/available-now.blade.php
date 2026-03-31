@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="availableToggle({
        initialStatus: @js((bool) $status),
        initialRemainingUses: @js((int) $remainingUses),
        initialExpiresAt: @js($expiresAt ?? null),
        updateUrl: @js(route('available.update-status'))
    })"
>
    <div class="mx-auto max-w-3xl">
        <a
            href="{{ url('/profile-setting') }}"
            class="mb-4 inline-flex items-center text-sm font-medium text-[#e04ecb] hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span>
            Back to profile settings
        </a>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="mb-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                Available Now
            </h1>

            <p class="mb-2 text-gray-600">
                Mark yourself available for immediate enquiries and improve visibility.
            </p>

            <p class="mb-6 text-sm font-medium text-pink-600">
                Promote your availability twice a day for two hours.
            </p>

            <div class="mb-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-500">Remaining uses today</p>
                    <p class="mt-2 text-2xl font-bold text-pink-600" x-text="remainingUses"></p>
                </div>

                <div
                    x-show="enabled"
                    x-cloak
                    x-transition
                    class="rounded-2xl border border-green-100 bg-green-50 p-4"
                >
                    <p class="text-sm font-medium text-green-700">Time left</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-green-600" x-text="countdown"></p>
                </div>
            </div>

            <button
                type="button"
                @click="toggleStatus()"
                :disabled="loading || (!enabled && remainingUses <= 0)"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 font-semibold transition sm:w-auto"
                :class="enabled
                    ? 'bg-pink-600 text-white hover:bg-pink-700'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50'"
            >
                <span
                    x-show="!loading"
                    x-cloak
                    x-text="enabled ? 'Available now enabled' : 'Enable available now'"
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
                x-show="!enabled && remainingUses <= 0"
                x-cloak
                x-transition
                class="mt-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800"
            >
                You have reached your daily limit for Available Now. Please try again tomorrow.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('profile/js/available-toggle.js') }}"></script>
@endpush
