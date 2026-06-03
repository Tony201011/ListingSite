@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div
        class="mx-auto max-w-5xl"
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
            class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-base font-medium text-[#e04ecb] transition-colors hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="mb-10 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
            My Profiles
        </h1>

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

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-md sm:p-10">
            @if($profiles->isEmpty())
                <div class="mb-8 rounded-xl border border-gray-100 bg-gray-50 px-5 py-6 text-gray-600">
                    You have no profiles yet. Create your first profile to get started.
                </div>
            @else
                <div class="mb-8 space-y-4">
                    @foreach($profiles as $profile)
                        @php
                            $state = $onlineStates[$profile->id] ?? ['onlineStatus' => false, 'expiresAt' => null];
                        @endphp

                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-6"
                            x-data="profileOnlineToggle({
                                profileId: @js($profile->id),
                                initialStatus: @js((bool) $state['onlineStatus']),
                                updateUrl: @js(route('profiles.online-status', $profile)),
                                csrfToken: @js(csrf_token())
                            })"
                        >
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                <a
                                    href="{{ route('profiles.switch', $profile) }}"
                                    class="flex min-w-0 items-center gap-4 rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-400"
                                    title="Open {{ $profile->name }} in My Profile"
                                >
                                    <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-full border border-gray-200 bg-white">
                                        @if($profile->primaryProfileImage?->thumbnail_url)
                                            <img
                                                src="{{ $profile->primaryProfileImage->thumbnail_url }}"
                                                alt="{{ $profile->name }}"
                                                class="h-full w-full object-cover"
                                            >
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#e04ecb] to-[#c13ab0]">
                                                <span class="select-none text-xl font-bold text-white">
                                                    {{ strtoupper(substr($profile->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate text-xl font-bold text-gray-900">
                                                {{ $profile->name }}
                                            </p>

                                            @if($profile->profile_status === 'approved')
                                                <span
                                                    class="h-2.5 w-2.5 rounded-full border-2 border-white shadow-sm"
                                                    :class="online ? 'bg-green-400' : 'bg-gray-300'"
                                                    :title="online ? 'Available Now' : 'Not Available'"
                                                    :aria-label="online ? 'Status: Available Now' : 'Status: Not Available'"
                                                    role="img"
                                                ></span>
                                            @endif
                                        </div>

                                        <p class="truncate text-sm text-gray-500">
                                            /{{ $profile->slug }}
                                        </p>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span
                                                class="inline-flex rounded-full px-3 py-1 text-sm font-semibold
                                                    @if($profile->profile_status === 'approved') bg-green-100 text-green-700
                                                    @elseif($profile->profile_status === 'rejected') bg-red-100 text-red-700
                                                    @else bg-yellow-100 text-yellow-700
                                                    @endif"
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
                                                <span class="inline-flex rounded-full bg-pink-100 px-3 py-1 text-sm font-semibold text-pink-700">
                                                    Selected
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>

                                <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                                    @if($profile->profile_status === 'approved')
                                        <button
                                            type="button"
                                            @click="toggleOnline"
                                            :disabled="loading"
                                            class="rounded-lg px-4 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-50"
                                            :class="online
                                                ? 'bg-green-600 text-white hover:bg-green-700'
                                                : 'bg-gray-200 text-gray-800 hover:bg-gray-300'"
                                        >
                                            <span x-show="loading" class="inline-flex items-center gap-1">
                                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                                Updating
                                            </span>
                                            <span x-show="!loading" x-text="online ? 'Go Not Available' : 'Go Available Now'"></span>
                                        </button>
                                    @endif

                                    <form method="POST" action="{{ route('profiles.switch-edit', $profile) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-700"
                                        >
                                            Edit
                                        </button>
                                    </form>

                                    @if((int)$activeProfileId !== $profile->id)
                                        <form method="POST" action="{{ route('profiles.switch', $profile) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700"
                                            >
                                                Switch
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('profiles.destroy', $profile) }}"
                                        data-profile-delete-form
                                        data-profile-name="{{ $profile->name }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-lg bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                                            aria-label="Delete profile {{ $profile->name }}"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($profile->profile_status !== 'approved')
                                <div
                                    class="mt-4 rounded-lg border px-4 py-3 text-sm font-medium
                                    @if($profile->profile_status === 'rejected') border-red-200 bg-red-50 text-red-700
                                    @else border-yellow-200 bg-yellow-50 text-yellow-700
                                    @endif"
                                    role="alert"
                                    aria-live="polite"
                                >
                                    @if($profile->profile_status === 'rejected')
                                        ✗ This profile has been rejected and is not visible in search results. Please contact support for assistance.
                                    @else
                                        ⏳ This profile is awaiting admin approval. It will <strong>not appear in search results</strong> until it has been approved.
                                    @endif
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
                    @endforeach
                </div>
            @endif

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button
                    type="button"
                    @click="showCreateModal = true; createName = ''; createPhone = ''; createErrors = []"
                    class="inline-flex items-center justify-center rounded-full bg-pink-600 px-8 py-3 text-base font-bold text-white shadow-md transition hover:bg-pink-700"
                >
                    + Create New Profile
                </button>

                <a
                    href="{{ route('account.delete-page') }}"
                    class="inline-flex items-center justify-center rounded-full border border-rose-500 bg-white px-8 py-3 text-base font-semibold text-rose-600 transition hover:bg-rose-50"
                    data-delete-account-trigger
                    onclick="event.preventDefault()"
                >
                    Delete account altogether
                </a>
            </div>
        </div>

        {{-- Create New Profile Modal --}}
        <div
            x-show="showCreateModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            @keydown.escape.window="showCreateModal = false"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
                @click.stop
            >
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
                        <label class="mb-1 block text-sm font-semibold text-[#e04ecb]">
                            Profile name <span class="text-red-500">*</span>
                        </label>
                        <input
                            name="name"
                            type="text"
                            x-model="createName"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-[#e04ecb] focus:outline-none focus:ring-2 focus:ring-[#e04ecb]/30"
                            placeholder="e.g. Jenny"
                            required
                            autofocus
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-[#e04ecb]">
                            Mobile number <span class="font-normal text-gray-400">(optional)</span>
                        </label>
                        <input
                            name="phone"
                            type="text"
                            x-model="createPhone"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-[#e04ecb] focus:outline-none focus:ring-2 focus:ring-[#e04ecb]/30"
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
                            class="w-full rounded-lg bg-pink-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-pink-700 sm:w-auto"
                        >
                            Create Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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