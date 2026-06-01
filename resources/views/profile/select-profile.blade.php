@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-white flex flex-col items-center justify-center px-4 py-16">

    <div class="mb-10 text-center">
        <h1 class="text-4xl font-bold text-gray-900 tracking-tight">Who's advertising?</h1>
        <p class="mt-2 text-gray-500 text-sm">Choose a profile to manage</p>
    </div>

    <div class="flex flex-wrap justify-center gap-6">
        @foreach($profiles as $profile)
            @php
                $state = $onlineStates[$profile->id] ?? ['onlineStatus' => false, 'remainingUses' => 0, 'expiresAt' => null];
                $isOnline = (bool) $state['onlineStatus'];
                $isActive = (int) $activeProfileId === $profile->id;
            @endphp
            <form
                method="POST"
                action="{{ route('profiles.switch', $profile) }}"
                class="group"
                x-data="profileOnlineToggle({
                    profileId: @js($profile->id),
                    initialStatus: @js($isOnline),
                    updateUrl: @js(route('profiles.online-status', $profile)),
                    csrfToken: @js(csrf_token())
                })"
            >
                @csrf
                <button
                    type="submit"
                    class="flex flex-col items-center gap-3 focus:outline-none"
                    title="{{ $profile->name }}"
                >
                    {{-- Avatar circle --}}
                    <div class="relative h-28 w-28 rounded-xl overflow-hidden border-4 border-transparent transition-all duration-200 group-hover:border-[#e04ecb] group-focus:border-[#e04ecb]">
                        @if($profile->primaryProfileImage?->thumbnail_url)
                            <img
                                src="{{ $profile->primaryProfileImage->thumbnail_url }}"
                                alt="{{ $profile->name }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-[#e04ecb] to-[#c13ab0]">
                                <span class="text-4xl font-bold text-white select-none">
                                    {{ strtoupper(substr($profile->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif

                        {{-- Online indicator dot (matches my-profiles page) --}}
                        <span
                            class="absolute bottom-1 right-1 h-3 w-3 rounded-full border-2 border-white shadow-sm"
                            :class="online ? 'bg-green-400' : 'bg-gray-300'"
                            :title="online ? 'Available Now' : 'Not Available'"
                            role="img"
                        ></span>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2">
                            <p class="text-gray-700 text-sm font-medium group-hover:text-gray-900 transition-colors">
                                {{ $profile->name }}
                            </p>
                            @if($isActive)
                                <span class="rounded-full bg-pink-100 px-2 py-0.5 text-xs font-semibold text-pink-700" title="This is the profile you are currently managing.">
                                    Selected
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-400 text-xs mt-0.5 group-hover:text-gray-600 transition-colors">
                            /{{ $profile->slug }}
                        </p>
                        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium
                            @if($profile->profile_status === 'approved') bg-green-100 text-green-700
                            @elseif($profile->profile_status === 'rejected') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700
                            @endif
                        "
                        title="@if($profile->profile_status === 'approved')Live – visible in search results.@elseif($profile->profile_status === 'rejected')Rejected – not visible publicly.@else Pending admin approval – not yet visible in search results.@endif">
                            @if($profile->profile_status === 'approved')
                                ✓ Approved – Live
                            @elseif($profile->profile_status === 'rejected')
                                ✗ Rejected
                            @else
                                ⏳ Pending Approval
                            @endif
                        </span>
                    </div>
                </button>
            </form>
        @endforeach

        {{-- Add new profile card --}}
        <form method="POST" action="{{ route('profiles.store') }}" class="group">
            @csrf
            <button
                type="submit"
                class="flex flex-col items-center gap-3 focus:outline-none"
                title="Add new profile"
            >
                <div class="h-28 w-28 rounded-xl border-4 border-dashed border-gray-300 flex items-center justify-center transition-all duration-200 group-hover:border-[#e04ecb] group-focus:border-[#e04ecb] bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 group-hover:text-[#e04ecb] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm font-medium group-hover:text-gray-700 transition-colors">
                    Add Profile
                </p>
            </button>
        </form>
    </div>

    <div class="mt-12 flex items-center gap-4 text-sm text-gray-400">
        <a href="{{ route('profiles.index') }}" class="hover:text-gray-700 transition-colors">
            Manage profiles
        </a>
        <span>·</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="button" class="hover:text-gray-700 transition-colors" @click="confirmSignOut($el.closest('form'))">
                Sign out
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/profile-online-sync.js') }}?v={{ filemtime(public_path('profile/js/profile-online-sync.js')) }}"></script>
<script src="{{ asset('profile/js/my-profiles-online.js') }}?v={{ filemtime(public_path('profile/js/my-profiles-online.js')) }}"></script>
@endpush
