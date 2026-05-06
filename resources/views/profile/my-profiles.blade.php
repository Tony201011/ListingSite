@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-900 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <button
            type="button"
            onclick="window.history.back()"
            class="mb-4 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-[#e04ecb] transition-colors hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="mb-8 text-3xl font-bold tracking-tight text-white sm:text-4xl">
            My Profiles
        </h1>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-700 bg-green-900/50 px-4 py-3 text-sm font-medium text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-700 bg-red-900/50 px-4 py-3 text-sm font-medium text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 overflow-hidden rounded-2xl border border-gray-700 bg-gray-800 shadow-lg">
            <div class="p-6 sm:p-8">

                @if($profiles->isEmpty())
                    <p class="mb-6 text-gray-400">You have no profiles yet. Create your first profile to get started.</p>
                @else
                    <div class="mb-6 space-y-3">
                        @foreach($profiles as $profile)
                            @php $state = $onlineStates[$profile->id] ?? ['onlineStatus' => false, 'remainingUses' => 0, 'expiresAt' => null]; @endphp
                            <div
                                class="rounded-xl border border-gray-700 bg-gray-700/50 px-4 py-4 transition hover:bg-gray-700"
                                x-data="profileOnlineToggle({
                                    profileId: @js($profile->id),
                                    initialStatus: @js((bool) $state['onlineStatus']),
                                    initialRemainingUses: @js($state['remainingUses']),
                                    initialExpiresAt: @js($state['expiresAt'] ?? null),
                                    updateUrl: @js(route('profiles.online-status', $profile)),
                                    csrfToken: @js(csrf_token())
                                })"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    {{-- Profile info --}}
                                    <div class="flex items-center gap-3">
                                        {{-- Profile avatar --}}
                                        <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full border border-gray-600">
                                            @if($profile->primaryProfileImage?->thumbnail_url)
                                                <img
                                                    src="{{ $profile->primaryProfileImage->thumbnail_url }}"
                                                    alt="{{ $profile->name }}"
                                                    class="h-full w-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#e04ecb] to-[#c13ab0]">
                                                    <span class="select-none text-lg font-bold text-white">{{ strtoupper(substr($profile->name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-semibold text-white">{{ $profile->name }}</p>
                                                {{-- Online indicator dot (only meaningful when profile is approved) --}}
                                                @if($profile->profile_status === 'approved')
                                                    <span
                                                        class="h-2.5 w-2.5 rounded-full border-2 border-gray-700 shadow-sm"
                                                        :class="online ? 'bg-green-400' : 'bg-gray-500'"
                                                        :title="online ? 'Online Now' : 'Offline'"
                                                        :aria-label="online ? 'Status: Online Now' : 'Status: Offline'"
                                                        role="img"
                                                    ></span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-400">/{{ $profile->slug }}</p>
                                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                <span
                                                    class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                                        @if($profile->profile_status === 'approved') bg-green-900/60 text-green-400
                                                        @elseif($profile->profile_status === 'rejected') bg-red-900/60 text-red-400
                                                        @else bg-yellow-900/60 text-yellow-400
                                                        @endif
                                                    "
                                                    title="@if($profile->profile_status === 'approved')Your listing is live and visible in search results.@elseif($profile->profile_status === 'rejected')Your listing has been rejected. Please contact support.@else Your listing is awaiting admin approval before it becomes visible in search results.@endif"
                                                >
                                                    @if($profile->profile_status === 'approved')
                                                        ✓ Approved – Live
                                                    @elseif($profile->profile_status === 'rejected')
                                                        ✗ Rejected
                                                    @else
                                                        ⏳ Pending Approval
                                                    @endif
                                                </span>
                                                @if((int)$activeProfileId === $profile->id)
                                                    <span class="inline-block rounded-full bg-pink-900/60 px-2.5 py-0.5 text-xs font-semibold text-pink-400" title="This is the profile you are currently managing.">
                                                        Selected
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action buttons --}}
                                    <div class="flex flex-wrap items-center gap-2">
                                        {{-- Online Now toggle (only available for approved profiles) --}}
                                        @if($profile->profile_status === 'approved')
                                            <div class="flex flex-col items-start gap-1 sm:items-end">
                                                <button
                                                    type="button"
                                                    @click="toggleOnline"
                                                    :disabled="loading || (!online && remainingUses <= 0)"
                                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                                                    :class="online
                                                        ? 'bg-green-600 text-white hover:bg-green-700'
                                                        : 'bg-gray-600 text-gray-200 hover:bg-gray-500 disabled:cursor-not-allowed disabled:opacity-50'"
                                                >
                                                    <span x-show="loading" class="flex items-center gap-1">
                                                        <svg class="h-3 w-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                        </svg>
                                                    </span>
                                                    <span x-show="!loading" x-text="online ? 'Online Now' : 'Go Online'"></span>
                                                </button>
                                                <span class="text-xs text-gray-400" x-show="online && countdown !== '00:00'" x-text="countdown" aria-label="Time remaining" aria-live="polite"></span>
                                                <span class="text-xs text-gray-400" x-show="!online" x-text="remainingUses + ' uses left'"></span>
                                            </div>
                                        @endif

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
                                                    class="rounded-lg border border-rose-800 bg-rose-900/40 px-3 py-1.5 text-sm font-medium text-rose-400 transition hover:bg-rose-900/70"
                                                    aria-label="Delete profile {{ $profile->name }}"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                {{-- Visibility notice for non-approved profiles --}}
                                @if($profile->profile_status !== 'approved')
                                    <div class="mt-3 rounded-lg border px-3 py-2 text-xs font-medium
                                        @if($profile->profile_status === 'rejected') border-red-800 bg-red-900/40 text-red-400
                                        @else border-yellow-800 bg-yellow-900/40 text-yellow-400
                                        @endif"
                                        role="alert"
                                        aria-live="polite"
                                    >
                                        @if($profile->profile_status === 'rejected')
                                            ✗ This profile has been rejected and is not visible in search results. Please contact support for assistance.
                                        @else
                                            ⏳ This profile is awaiting admin approval. It will <strong>not appear in search results</strong> until it has been approved. You will be notified once the review is complete.
                                        @endif
                                    </div>
                                @endif

                                {{-- Inline message --}}
                                <div class="mt-2" x-show="message" x-transition>
                                    <div
                                        class="rounded-lg border px-3 py-2 text-xs font-medium"
                                        :class="messageType === 'success'
                                            ? 'border-green-700 bg-green-900/50 text-green-300'
                                            : 'border-red-700 bg-red-900/50 text-red-300'"
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
                        class="inline-flex items-center justify-center rounded-full border border-transparent bg-pink-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 focus:ring-offset-gray-800"
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
