@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gradient-to-b from-pink-50 via-white to-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="onlineNowToggle({
    initialStatus: @js((bool) $onlineStatus),
    initialRemainingUses: @js($remainingUses),
    initialExpiresAt: @js($expiresAt ?? null),
    updateUrl: @js(route('online.update-status')),
    csrfToken: @js(csrf_token())
    })"
>
    <div class="mx-auto max-w-3xl">
        @include('profile.partials.back-to-settings')

        <div class="overflow-hidden rounded-3xl border border-pink-100 bg-white shadow-lg shadow-pink-100/40">
            <div class="border-b border-pink-100 bg-gradient-to-r from-pink-600 to-fuchsia-500 px-6 py-6 text-white sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold sm:text-3xl">Online Now</h1>
                        <p class="mt-2 text-sm text-pink-50 sm:text-base">
                            Mark yourself available for online enquiries and improve visibility.
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold"
                        :class="enabled ? 'bg-white text-pink-600' : 'bg-pink-500/30 text-white'"
                    >
                        <span
                            class="h-2.5 w-2.5 rounded-full"
                            :class="enabled ? 'bg-green-500' : 'bg-white/70'"
                        ></span>
                        <span x-text="enabled ? 'Currently Online' : 'Currently Offline'"></span>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm font-medium text-gray-500">Daily usage rule</p>
                        <p class="mt-2 text-base font-semibold text-gray-900">
                            Use this feature up to 4 times a day for 60 minutes.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm font-medium text-gray-500">Remaining uses today</p>
                        <p class="mt-2 text-2xl font-bold text-pink-600" x-text="remainingUses"></p>
                    </div>
                </div>

                <div
                    class="mt-4 rounded-2xl border border-green-100 bg-green-50 p-4"
                    x-show="enabled"
                    x-transition
                >
                    <p class="text-sm font-medium text-green-700">Online session timer</p>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="rounded-xl bg-white px-4 py-3 shadow-sm border border-green-100">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Time left</p>
                            <p class="mt-1 text-2xl font-bold text-green-600 tabular-nums" x-text="countdown"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button
                        type="button"
                        @click="toggleStatus"
                        :disabled="loading || (!enabled && remainingUses <= 0)"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold shadow-sm transition duration-200 sm:w-auto"
                        :class="enabled
                            ? 'bg-pink-600 text-white hover:bg-pink-700'
                            : 'bg-gray-900 text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500'"
                    >
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Updating...</span>
                            </span>
                        </template>

                        <template x-if="!loading">
                            <span x-text="enabled ? 'Disable Online Now' : 'Enable Online Now'"></span>
                        </template>
                    </button>
                </div>

                <div class="mt-4" x-show="message" x-transition>
                    <div
                        class="rounded-xl border px-4 py-3 text-sm font-medium"
                        :class="messageType === 'success'
                            ? 'border-green-200 bg-green-50 text-green-700'
                            : 'border-red-200 bg-red-50 text-red-700'"
                        x-text="message"
                    ></div>
                </div>

                <div
                    class="mt-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800"
                    x-show="!enabled && remainingUses <= 0"
                    x-transition
                >
                    You have reached your daily limit for Online Now. Please try again tomorrow.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('profile/js/online-now-toggle.js') }}"></script>
@endpush

@endsection
