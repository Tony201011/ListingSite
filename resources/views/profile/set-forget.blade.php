@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="setAndForget({
        initialOnlineNowEnabled: @js((bool) $online_now_enabled),
        initialOnlineNowDays: @js($online_now_days),
        initialOnlineNowTime: @js($online_now_time),
        initialAvailableNowEnabled: @js((bool) $available_now_enabled),
        initialAvailableNowDays: @js($available_now_days),
        initialAvailableNowTime: @js($available_now_time),
        saveUrl: @js(route('set-and-forget.save')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="mx-auto max-w-3xl">
        @include('profile.partials.back-to-settings')

        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gradient-to-r from-pink-600 to-fuchsia-500 px-6 py-6 text-white sm:px-8">
                <h1 class="text-2xl font-bold sm:text-3xl">Set &amp; Forget</h1>
                <p class="mt-2 text-sm text-pink-50 sm:text-base">
                    Automate your Online Now and Available Now statuses at your preferred times.
                </p>
            </div>

            <div class="p-6 sm:p-8 space-y-8">

                {{-- Online Now Automation --}}
                <div class="rounded-2xl border border-gray-200 p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Online Now</h2>
                            <p class="text-sm text-gray-500 mt-1">Automatically activate Online Now on selected days at a set time.</p>
                        </div>
                        <button
                            type="button"
                            @click="onlineNowEnabled = !onlineNowEnabled"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                            :class="onlineNowEnabled ? 'bg-pink-600' : 'bg-gray-200'"
                            role="switch"
                            :aria-checked="onlineNowEnabled"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="onlineNowEnabled ? 'translate-x-5' : 'translate-x-0'"
                            ></span>
                        </button>
                    </div>

                    <div x-show="onlineNowEnabled" x-transition class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-3">Select days</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($days as $day)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :value="'{{ $day }}'" x-model="onlineNowDays">
                                        <span class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm font-medium transition peer-checked:border-pink-600 peer-checked:bg-pink-50 peer-checked:text-pink-700 border-gray-200 bg-white text-gray-600 hover:bg-gray-50">
                                            {{ substr($day, 0, 3) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Activation time</label>
                            <select
                                x-model="onlineNowTime"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-pink-500 focus:ring-2 focus:ring-pink-200 sm:w-48"
                            >
                                <option value="">Select a time</option>
                                @for($i = 0; $i <= 23; $i++)
                                    @for($m = 0; $m < 60; $m += 15)
                                        @php $t = str_pad($i, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Available Now Automation --}}
                <div class="rounded-2xl border border-gray-200 p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Available Now</h2>
                            <p class="text-sm text-gray-500 mt-1">Automatically activate Available Now on selected days at a set time.</p>
                        </div>
                        <button
                            type="button"
                            @click="availableNowEnabled = !availableNowEnabled"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                            :class="availableNowEnabled ? 'bg-pink-600' : 'bg-gray-200'"
                            role="switch"
                            :aria-checked="availableNowEnabled"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="availableNowEnabled ? 'translate-x-5' : 'translate-x-0'"
                            ></span>
                        </button>
                    </div>

                    <div x-show="availableNowEnabled" x-transition class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-3">Select days</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($days as $day)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :value="'{{ $day }}'" x-model="availableNowDays">
                                        <span class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm font-medium transition peer-checked:border-pink-600 peer-checked:bg-pink-50 peer-checked:text-pink-700 border-gray-200 bg-white text-gray-600 hover:bg-gray-50">
                                            {{ substr($day, 0, 3) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Activation time</label>
                            <select
                                x-model="availableNowTime"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-pink-500 focus:ring-2 focus:ring-pink-200 sm:w-48"
                            >
                                <option value="">Select a time</option>
                                @for($i = 0; $i <= 23; $i++)
                                    @for($m = 0; $m < 60; $m += 15)
                                        @php $t = str_pad($i, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Save button --}}
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        @click="save"
                        :disabled="loading"
                        class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 transition"
                    >
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </template>
                        <template x-if="!loading">
                            <span>Save automation</span>
                        </template>
                    </button>

                    <div x-show="message" x-transition>
                        <span
                            class="text-sm font-medium"
                            :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'"
                            x-text="message"
                        ></span>
                    </div>
                </div>

                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                    <strong>How it works:</strong> When automation is enabled, the system will automatically activate Online Now or Available Now at your chosen time on the selected days. Daily usage limits still apply.
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('profile/js/set-forget.js') }}"></script>
@endpush

@endsection

