@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
        <h1 class="mb-8 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
            My Profiles
        </h1>

        <div x-data="{ showSuccess: true }">
            @if(session('success'))
                <div
                    x-show="showSuccess"
                    x-transition
                    class="mb-4 flex items-start justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                >
                    <span>{{ session('success') }}</span>
                    <button type="button" @click="showSuccess = false" class="text-lg leading-none text-green-500 hover:text-green-700">&times;</button>
                </div>
            @endif
        </div>

        <div class="mb-6 flex items-center justify-between">
            <p class="text-gray-600">
                You have <strong>{{ $profiles->count() }}</strong> {{ Str::plural('profile', $profiles->count()) }}.
            </p>
            <a
                href="{{ route('create-profile') }}"
                class="inline-flex items-center justify-center rounded-full bg-pink-600 px-6 py-2.5 font-semibold text-white shadow-lg shadow-pink-600/30 transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
            >
                + Create New Profile
            </a>
        </div>

        @if($profiles->isEmpty())
            <div class="rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                <p class="mb-4 text-lg text-gray-600">You haven't created a profile yet.</p>
                <a
                    href="{{ route('create-profile') }}"
                    class="inline-flex items-center justify-center rounded-full bg-pink-600 px-8 py-3.5 font-medium text-white shadow-lg shadow-pink-600/30 transition hover:bg-pink-700"
                >
                    Create Your First Profile
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($profiles as $profile)
                    @php
                        $stepOneCompleted =
                            ! empty($profile->introduction_line) &&
                            ! empty($profile->profile_text) &&
                            ! is_null($profile->age_group_id) &&
                            ! is_null($profile->hair_color_id) &&
                            ! is_null($profile->hair_length_id) &&
                            ! is_null($profile->ethnicity_id) &&
                            ! is_null($profile->body_type_id) &&
                            ! is_null($profile->bust_size_id) &&
                            ! is_null($profile->your_length_id) &&
                            ! empty($profile->availability) &&
                            ! empty($profile->contact_method) &&
                            ! empty($profile->phone_contact_preference) &&
                            ! empty($profile->time_waster_shield) &&
                            ! empty($profile->primary_identity) &&
                            ! empty($profile->attributes) &&
                            ! empty($profile->services_style) &&
                            ! empty($profile->services_provided);
                    @endphp
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h2 class="text-lg font-bold text-gray-900">{{ $profile->name }}</h2>
                                    @if($profile->profile_status === 'approved')
                                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Approved</span>
                                    @elseif($profile->profile_status === 'pending')
                                        <span class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-700">Pending</span>
                                    @elseif($profile->profile_status === 'rejected')
                                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Rejected</span>
                                    @endif
                                    @if($profile->is_featured)
                                        <span class="rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-semibold text-pink-700">Featured</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Slug: <span class="font-medium text-gray-700">{{ $profile->slug }}</span>
                                </p>
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        @if($stepOneCompleted)
                                            <span class="text-green-500">✓</span> Profile text complete
                                        @else
                                            <span class="text-gray-400">○</span> Profile text incomplete
                                        @endif
                                    </span>
                                    <span>Created {{ $profile->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a
                                    href="{{ route('my-profile.show', $profile) }}"
                                    class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                                >
                                    Dashboard
                                </a>
                                <a
                                    href="{{ route('edit-profile', $profile) }}"
                                    class="inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-pink-700"
                                >
                                    Edit Profile
                                </a>
                                @if($profile->slug)
                                    <a
                                        href="{{ route('profile.show', ['slug' => $profile->slug]) }}"
                                        target="_blank"
                                        class="inline-flex items-center rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                                    >
                                        View Public
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
