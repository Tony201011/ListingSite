@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <button
            type="button"
            onclick="window.history.back()"
            class="mb-4 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-[#e04ecb] transition-colors hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="mb-8 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
            My Profiles
        </h1>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="p-6 sm:p-8">

                @if($profiles->isEmpty())
                    <p class="mb-4 text-gray-600">You have no profiles yet. Create your first profile to get started.</p>
                @else
                    <div class="mb-6 space-y-3">
                        @foreach($profiles as $profile)
                            @php $state = $onlineStates[$profile->id] ?? ['onlineStatus' => false, 'remainingUses' => 0, 'expiresAt' => null]; @endphp
                            <div
                                class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 transition hover:bg-gray-100"
                                x-data="profileOnlineToggle({
                                    profileId: @js($profile->id),
                                    initialStatus: @js((bool) $state['onlineStatus']),
                                    initialRemainingUses: @js($state['remainingUses']),
                                    initialExpiresAt: @js($state['expiresAt'] ?? null),
                                    updateUrl: @js(route('profiles.online-status', $profile)),
                                    csrfToken: @js(csrf_token())
                                })"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-gray-900">{{ $profile->name }}</p>
                                                {{-- Online indicator dot --}}
                                                <span
                                                    class="h-2.5 w-2.5 rounded-full border-2 border-white shadow-sm"
                                                    :class="online ? 'bg-green-400' : 'bg-gray-300'"
                                                    :title="online ? 'Online' : 'Offline'"
                                                ></span>
                                            </div>
                                            <p class="text-xs text-gray-500">/{{ $profile->slug }}</p>
                                            <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                                @if($profile->profile_status === 'approved') bg-green-100 text-green-700
                                                @elseif($profile->profile_status === 'rejected') bg-red-100 text-red-700
                                                @else bg-yellow-100 text-yellow-700
                                                @endif
                                            ">
                                                {{ ucfirst($profile->profile_status ?? 'pending') }}
                                            </span>
                                        </div>

                                        @if((int)$activeProfileId === $profile->id)
                                            <span class="ml-2 rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-semibold text-pink-700">
                                                Active
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        {{-- Online Now toggle --}}
                                        <div class="flex flex-col items-end gap-1">
                                            <button
                                                type="button"
                                                @click="toggleOnline"
                                                :disabled="loading || (!online && remainingUses <= 0)"
                                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                                                :class="online
                                                    ? 'bg-green-600 text-white hover:bg-green-700'
                                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50'"
                                            >
                                                <span x-show="loading" class="flex items-center gap-1">
                                                    <svg class="h-3 w-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </span>
                                                <span x-show="!loading" x-text="online ? 'Online' : 'Go Online'"></span>
                                            </button>
                                            <span class="text-xs text-gray-400" x-show="online && countdown !== '00:00'" x-text="countdown"></span>
                                            <span class="text-xs text-gray-400" x-show="!online" x-text="remainingUses + ' uses left'"></span>
                                        </div>

                                        @if((int)$activeProfileId !== $profile->id)
                                            <form method="POST" action="{{ route('profiles.switch', $profile) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-pink-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-pink-700"
                                                >
                                                    Switch
                                                </button>
                                            </form>
                                        @endif

                                        @if($profiles->count() > 1)
                                            <form
                                                method="POST"
                                                action="{{ route('profiles.destroy', $profile) }}"
                                                x-data
                                                @submit.prevent="if (confirm('Delete this profile? This cannot be undone.')) $el.submit()"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
                                                    aria-label="Delete profile {{ $profile->name }}"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                {{-- Inline message --}}
                                <div class="mt-2" x-show="message" x-transition>
                                    <div
                                        class="rounded-lg border px-3 py-2 text-xs font-medium"
                                        :class="messageType === 'success'
                                            ? 'border-green-200 bg-green-50 text-green-700'
                                            : 'border-red-200 bg-red-50 text-red-700'"
                                        x-text="message"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('profiles.store') }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full border border-transparent bg-pink-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                    >
                        + Create New Profile
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('profile/js/my-profiles-online.js') }}"></script>
@endpush
@endsection
