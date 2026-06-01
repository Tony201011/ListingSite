@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gradient-to-b from-pink-50 via-white to-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="{
        activeTab: 'available-status',
        onlineComp: null,
        availableComp: null,
        visibilityComp: null,
    }"
>
    <div class="mx-auto max-w-3xl">
        @include('profile.partials.back-to-settings')

        <div class="overflow-hidden rounded-3xl border border-pink-100 bg-white shadow-lg shadow-pink-100/40">

            {{-- Tab header --}}
            <div class="border-b border-pink-100 bg-gradient-to-r from-pink-600 to-fuchsia-500 px-6 pt-6 text-white sm:px-8">
                <h1 class="mb-5 text-2xl font-bold sm:text-3xl">Status &amp; Visibility</h1>
                <div class="flex gap-1 overflow-x-auto">
                    <button
                        type="button"
                        @click="activeTab = 'available-status'"
                        class="whitespace-nowrap rounded-t-xl px-4 py-2.5 text-sm font-semibold transition"
                        :class="activeTab === 'available-status'
                            ? 'bg-white text-pink-600'
                            : 'text-white/80 hover:text-white hover:bg-white/20'"
                    >
                        Available Status
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'profile-visibility'"
                        class="whitespace-nowrap rounded-t-xl px-4 py-2.5 text-sm font-semibold transition"
                        :class="activeTab === 'profile-visibility'
                            ? 'bg-white text-pink-600'
                            : 'text-white/80 hover:text-white hover:bg-white/20'"
                    >
                        Profile Visibility
                    </button>
                </div>
            </div>

            {{-- Available Status tab --}}
            <div
                x-show="activeTab === 'available-status'"
                x-cloak
                x-data="availableToggle({
                    initialStatus: @js((bool) $onlineStatus),
                    initialStartedAt: @js($onlineStartedAt ?? null),
                    initialBlockedBalance: false,
                    updateUrl: @js(route('available.update-status'))
                })"
                class="p-6 sm:p-8"
            >
                <div class="mb-4 flex items-center gap-3">
                    <div
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold"
                        :class="enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                    >
                        <span class="h-2.5 w-2.5 rounded-full" :class="enabled ? 'bg-green-500' : 'bg-gray-400'"></span>
                        <span x-text="enabled ? 'Available Now enabled' : 'Available Now disabled'"></span>
                    </div>
                </div>

                <p class="mb-6 text-gray-600">
                    Manually control whether your profile shows as Available Now.
                </p>

                <div class="mt-6">
                    <button
                        type="button"
                        @click="toggleStatus"
                        :disabled="loading"
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
                            <span x-text="enabled ? 'Disable Available Now' : 'Enable Available Now'"></span>
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
            </div>

            {{-- Profile Visibility tab --}}
            <div
                x-show="activeTab === 'profile-visibility'"
                x-cloak
                x-data="hideToggle({
                    initialStatus: @js((bool) $visibilityStatus),
                    updateUrl: @js(route('update-hide-show-profile')),
                    csrfToken: @js(csrf_token())
                })"
                class="p-6 sm:p-8"
            >
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
                    class="flex items-center justify-center gap-2 rounded-xl px-6 py-3 font-semibold shadow-sm transition"
                    :class="enabled
                        ? 'bg-pink-600 text-white hover:bg-pink-700'
                        : 'bg-gray-900 text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50'"
                >
                    <span x-show="!loading" x-cloak x-text="enabled ? 'Show profile' : 'Hide profile'"></span>

                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating...
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/hide-toggle.js') }}?v={{ filemtime(public_path('profile/js/hide-toggle.js')) }}"></script>
<script src="{{ asset('profile/js/available-toggle.js') }}?v={{ filemtime(public_path('profile/js/available-toggle.js')) }}"></script>
@endpush
