@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div
            class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8"
            x-data="{
                showCreateModal: false,
                createName: '',
                createPhone: '',
                createErrors: []
            }"
        >
            <button
                type="button"
                onclick="window.history.back()"
                class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-pink-500 transition-colors hover:text-pink-600"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">My Profiles</h1>

                <button
                    type="button"
                    @click="showCreateModal = true; createName = ''; createPhone = ''; createErrors = []"
                    class="inline-flex items-center justify-center rounded bg-pink-500 px-6 py-2 text-sm font-semibold text-white transition hover:bg-pink-600"
                >
                    + Create New Profile
                </button>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($profiles->isEmpty())
                <div class="rounded-2xl border border-gray-100 bg-gray-50 px-5 py-8 text-center text-gray-600">
                    You have no profiles yet. Create your first profile to get started.
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($profiles as $profile)
                        @php
                            $state = $onlineStates[$profile->id] ?? ['onlineStatus' => false, 'expiresAt' => null];
                            $profileImage = $profile->primaryProfileImage?->thumbnail_url;
                            $location = $profile->suburb ?? $profile->city ?? $profile->state ?? 'Australia';
                            $price = $profile->price ?? $profile->hourly_rate ?? $profile->rate ?? null;
                            $phone = $profile->phone ?? $profile->mobile ?? null;
                            $statusLabel = match ($profile->profile_status) {
                                'approved' => 'VERIFIED PROFILE',
                                'rejected' => 'REJECTED PROFILE',
                                default => 'PENDING PROFILE',
                            };
                        @endphp

                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-lg transition-shadow hover:shadow-xl"
                            x-data="profileOnlineToggle({
                                profileId: @js($profile->id),
                                initialStatus: @js((bool) $state['onlineStatus']),
                                updateUrl: @js(route('profiles.online-status', $profile)),
                                csrfToken: @js(csrf_token())
                            })"
                        >
                            <div class="bg-gray-100 py-2 text-center text-xs font-semibold tracking-wider text-gray-600">
                                {{ $statusLabel }}
                            </div>

                            <div class="relative">
                                @if($profileImage)
                                    <img
                                        src="{{ $profileImage }}"
                                        alt="{{ $profile->name }}"
                                        class="h-80 w-full object-cover"
                                    >
                                @else
                                    <div class="flex h-80 w-full items-center justify-center bg-gradient-to-br from-pink-500 to-pink-700">
                                        <span class="select-none text-7xl font-bold text-white">
                                            {{ strtoupper(substr($profile->name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif

                                <div class="absolute left-3 top-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold text-white
                                        @if($profile->profile_status === 'approved') bg-blue-600
                                        @elseif($profile->profile_status === 'rejected') bg-red-600
                                        @else bg-yellow-400 text-slate-900
                                        @endif"
                                    >
                                        @if($profile->profile_status === 'approved')
                                            PREMIUM
                                        @elseif($profile->profile_status === 'rejected')
                                            REJECTED
                                        @else
                                            PENDING
                                        @endif
                                    </span>
                                </div>

                                <div class="absolute right-3 top-3 flex gap-2">
                                    <a
                                        href="{{ route('profiles.switch', $profile) }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-white transition hover:bg-black/70"
                                        aria-label="View profile {{ $profile->name }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </a>

                                    @if($profile->profile_status === 'approved')
                                        <button
                                            type="button"
                                            @click="toggleOnline"
                                            :disabled="loading"
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-white transition hover:bg-black/70 disabled:cursor-not-allowed disabled:opacity-50"
                                            :aria-label="online ? 'Go Not Available' : 'Go Available Now'"
                                        >
                                            <svg x-show="!loading" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="online ? 'fill-pink-500 text-pink-500' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0016.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 002 8.5c0 2.3 1.5 4.05 3 5.5l7 7 7-7z" />
                                            </svg>
                                            <svg x-show="loading" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                    @if($profile->profile_status === 'approved')
                                        <div class="mb-2">
                                            <span
                                                class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-semibold text-white"
                                                :class="online ? 'bg-green-600' : 'bg-gray-600'"
                                            >
                                                <span class="h-2 w-2 rounded-full bg-white"></span>
                                                <span x-text="online ? 'Available now' : 'Not available'"></span>
                                            </span>
                                        </div>
                                    @endif

                                    <h3 class="flex items-center gap-2 text-lg font-bold text-white">
                                        {{ $profile->name }} - {{ $location }}
                                        @if($profile->profile_status === 'approved')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5" />
                                            </svg>
                                        @endif
                                    </h3>
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="mb-2 flex items-center justify-between gap-4">
                                    <div class="flex min-w-0 items-center gap-1 text-sm text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                        <span class="truncate">{{ $location }}</span>
                                    </div>

                                    @if($price)
                                        <span class="text-lg font-bold text-gray-800">${{ $price }}</span>
                                    @endif
                                </div>

                                <p class="mb-3 line-clamp-2 text-sm text-gray-600">
                                    @if($profile->profile_status === 'approved')
                                        Your profile is approved and visible in search results.
                                    @elseif($profile->profile_status === 'rejected')
                                        This profile has been rejected and is not visible in search results.
                                    @else
                                        This profile is awaiting admin approval and will not appear in search results yet.
                                    @endif
                                </p>

                                <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 01-2.18 2A19.79 19.79 0 0111.19 19 19.5 19.5 0 015 13a19.79 19.79 0 01-2.92-8.63A2 2 0 014.06 2h3a2 2 0 012 1.72c.12.9.33 1.77.63 2.61a2 2 0 01-.45 2.11L8 9.91a16 16 0 006 6l1.47-1.24a2 2 0 012.11-.45c.84.3 1.71.51 2.61.63A2 2 0 0122 16.92z" />
                                    </svg>
                                    <span>{{ $phone ?: 'No phone added' }}</span>
                                </div>

                                <div class="flex gap-2">
                                    <form class="flex-1" method="POST" action="{{ route('profiles.switch', $profile) }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded bg-blue-600 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                                            View
                                        </button>
                                    </form>

                                    <form class="flex-1" method="POST" action="{{ route('profiles.switch-edit', $profile) }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded bg-gray-200 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-300">
                                            Edit
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('profiles.destroy', $profile) }}"
                                        data-profile-delete-form
                                        data-profile-name="{{ $profile->name }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded border border-red-300 px-4 py-2 text-sm text-red-600 transition hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>

                                @if((int)$activeProfileId === $profile->id)
                                    <div class="mt-3 rounded bg-pink-50 px-3 py-2 text-center text-xs font-semibold text-pink-700">
                                        Selected profile
                                    </div>
                                @endif

                                <div class="mt-3" x-show="message" x-transition>
                                    <div
                                        class="rounded-lg border px-4 py-3 text-sm font-medium"
                                        :class="messageType === 'success'
                                            ? 'border-green-200 bg-green-50 text-green-700'
                                            : 'border-red-200 bg-red-50 text-red-700'"
                                        x-text="message"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a
                    href="{{ route('account.delete-page') }}"
                    class="inline-flex items-center justify-center rounded border border-red-300 bg-white px-5 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                    data-delete-account-trigger
                    onclick="event.preventDefault()"
                >
                    Delete account altogether
                </a>
            </div>

            {{-- Create New Profile Modal --}}
            <div
                x-show="showCreateModal"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                @keydown.escape.window="showCreateModal = false"
            >
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.stop>
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Create New Profile</h2>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="text-2xl leading-none text-gray-400 hover:text-gray-600"
                            aria-label="Close modal"
                        >
                            &times;
                        </button>
                    </div>

                    <p class="mb-4 text-sm text-gray-600">
                        Each profile can have its own name and phone number, so you can manage multiple listings from one account.
                    </p>

                    <template x-if="createErrors.length > 0">
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <template x-for="err in createErrors" :key="err">
                                <p x-text="err"></p>
                            </template>
                        </div>
                    </template>

                    <form
                        method="POST"
                        action="{{ route('profiles.store') }}"
                        @submit.prevent="
                            createErrors = [];
                            if (!createName.trim()) {
                                createErrors.push('Profile name is required.');
                                return;
                            }
                            $el.submit();
                        "
                        class="space-y-4"
                    >
                        @csrf

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-pink-500">
                                Profile name <span class="text-red-500">*</span>
                            </label>
                            <input
                                name="name"
                                type="text"
                                x-model="createName"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500/30"
                                placeholder="e.g. Jenny"
                                required
                                autofocus
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-pink-500">
                                Mobile number <span class="font-normal text-gray-400">(optional)</span>
                            </label>
                            <input
                                name="phone"
                                type="text"
                                x-model="createPhone"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500/30"
                                placeholder="e.g. 0400 000 000"
                            >
                        </div>

                        <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:items-center sm:justify-end">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="w-full rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:w-auto"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="w-full rounded-lg bg-pink-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-pink-600 sm:w-auto"
                            >
                                Create Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<form id="my-profiles-delete-account-form" action="{{ route('account.destroy') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <input type="hidden" name="password" id="my-profiles-delete-password">
    <input type="hidden" name="confirmation_text" id="my-profiles-delete-confirmation">
    <input type="hidden" name="delete_account_origin" value="my-profiles">
</form>

@push('scripts')
    <script src="{{ asset('profile/js/profile-online-sync.js') }}?v={{ filemtime(public_path('profile/js/profile-online-sync.js')) }}"></script>
    <script src="{{ asset('profile/js/my-profiles-online.js') }}?v={{ filemtime(public_path('profile/js/my-profiles-online.js')) }}"></script>

    <script>
        (function () {
            const deleteAccountForm = document.getElementById('my-profiles-delete-account-form');
            const deleteAccountPassword = document.getElementById('my-profiles-delete-password');
            const deleteAccountConfirmation = document.getElementById('my-profiles-delete-confirmation');

            if (deleteAccountForm && window.Swal) {
                document.querySelectorAll('[data-delete-account-trigger]').forEach(function (trigger) {
                    trigger.addEventListener('click', async function (e) {
                        e.preventDefault();

                        const result = await Swal.fire({
                            title: 'Delete account?',
                            html: `
                                <p style="margin-bottom:12px;font-size:14px;color:#4b5563;">
                                    We will send a secure confirmation link to your email.
                                    Your account will be deleted only after clicking that email link.
                                </p>
                                <input id="swal-delete-password" type="password" class="swal2-input" placeholder="Enter your password">
                                <input id="swal-delete-confirm" type="text" class="swal2-input" placeholder="Type DELETE">
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Send email',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#dc2626',
                            focusConfirm: false,
                            preConfirm: () => {
                                const password = document.getElementById('swal-delete-password')?.value ?? '';
                                const confirmationText = document.getElementById('swal-delete-confirm')?.value ?? '';

                                if (!password) {
                                    Swal.showValidationMessage('Password is required.');
                                    return false;
                                }

                                if (confirmationText !== 'DELETE') {
                                    Swal.showValidationMessage('Please type DELETE exactly.');
                                    return false;
                                }

                                return { password, confirmationText };
                            }
                        });

                        if (result.isConfirmed && result.value) {
                            deleteAccountPassword.value = result.value.password;
                            deleteAccountConfirmation.value = result.value.confirmationText;
                            deleteAccountForm.submit();
                        }
                    });
                });
            }

            if (window.Swal) {
                document.querySelectorAll('[data-profile-delete-form]').forEach(function (form) {
                    form.addEventListener('submit', async function (e) {
                        e.preventDefault();

                        const profileName = form.dataset.profileName || 'this profile';

                        const result = await Swal.fire({
                            title: 'Delete profile?',
                            text: 'Delete "' + profileName + '"? This cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Delete',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#dc2626',
                        });

                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                @if (session('delete_account_email_sent'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Email sent',
                        text: @json(session('success')),
                        confirmButtonColor: '#db2777',
                    });
                @endif

                @if (session('delete_account_email_error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Could not send email',
                        text: @json(session('error')),
                        confirmButtonColor: '#db2777',
                    });
                @endif

                @if (old('delete_account_origin') === 'my-profiles' && $errors->any())
                    Swal.fire({
                        icon: 'error',
                        title: 'Could not send email',
                        html: @json(collect($errors->all())->map(fn ($message) => '• '.$message)->implode('<br>')),
                        confirmButtonColor: '#db2777',
                    });
                @endif
            }
        })();
    </script>
@endpush
@endsection
