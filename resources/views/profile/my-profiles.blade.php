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

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($profiles->count() > 1)
            <form
                id="delete-selected-profiles-form"
                method="POST"
                action="{{ route('profiles.destroy-selected') }}"
                class="mb-4 flex flex-col gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                @csrf
                @method('DELETE')
                <p class="text-sm text-rose-800">
                    Select specific profiles below, then delete only the selected ones.
                </p>
                <button
                    type="submit"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700"
                >
                    Delete selected profiles
                </button>
            </form>
        @endif

        <div
            class="mb-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
            x-data="{
                showCreateModal: false,
                createName: '',
                createPhone: '',
                createErrors: []
            }"
        >
            <div class="p-6 sm:p-8">

                @if($profiles->isEmpty())
                    <p class="mb-4 text-gray-600">You have no profiles yet. Create your first profile to get started.</p>
                @else
                    <div class="mb-6 space-y-3">
                        @foreach($profiles as $profile)
                            @php $state = $onlineStates[$profile->id] ?? ['status' => false, 'expiresAt' => null]; @endphp
                            <div
                                class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 transition hover:bg-gray-100"
                                x-data="profileOnlineToggle({
                                    profileId: @js($profile->id),
                                    initialStatus: @js((bool) ($state['status'] ?? false)),
                                    updateUrl: @js(route('profiles.online-status', $profile)),
                                    csrfToken: @js(csrf_token())
                                })"
                            >
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        @if($profiles->count() > 1)
                                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-600">
                                                <input
                                                    type="checkbox"
                                                    name="profile_ids[]"
                                                    value="{{ $profile->id }}"
                                                    form="delete-selected-profiles-form"
                                                    class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                                                >
                                                Select
                                            </label>
                                        @endif

                                        {{-- Profile avatar --}}
                                        <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full border border-gray-200">
                                            @if($profile->primaryProfileImage?->thumbnail_url)
                                                <img
                                                    src="{{ $profile->primaryProfileImage->thumbnail_url }}"
                                                    alt="{{ $profile->name }}"
                                                    class="h-full w-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#e04ecb] to-[#c13ab0]">
                                                    <span class="text-lg font-bold text-white select-none">{{ strtoupper(substr($profile->name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="truncate font-semibold text-gray-900">{{ $profile->name }}</p>
                                                {{-- Online indicator dot (only meaningful when profile is approved) --}}
                                                @if($profile->profile_status === 'approved')
                                                    <span
                                                        class="h-2.5 w-2.5 rounded-full border-2 border-white shadow-sm"
                                                        :class="online ? 'bg-green-400' : 'bg-gray-300'"
                                                        :title="online ? 'Available Now' : 'Unavailable'"
                                                        :aria-label="online ? 'Status: Available Now' : 'Status: Unavailable'"
                                                        role="img"
                                                    ></span>
                                                @endif
                                            </div>
                                            <p class="truncate text-xs text-gray-500">/{{ $profile->slug }}</p>
                                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                <span
                                                    class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                                        @if($profile->profile_status === 'approved') bg-green-100 text-green-700
                                                        @elseif($profile->profile_status === 'rejected') bg-red-100 text-red-700
                                                        @else bg-yellow-100 text-yellow-700
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
                                                    <span class="inline-block rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-semibold text-pink-700" title="This is the profile you are currently managing.">
                                                        Selected
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                                        {{-- Available Now toggle (only available for approved profiles) --}}
                                        @if($profile->profile_status === 'approved')
                                            <div class="flex flex-col items-start gap-1 sm:items-end">
                                                <button
                                                    type="button"
                                                    @click="toggleOnline"
                                                    :disabled="loading"
                                                    class="w-full rounded-lg px-3 py-1.5 text-center text-xs font-semibold transition sm:w-auto"
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
                                                    <span x-show="!loading" x-text="online ? 'Disable Available Now' : 'Enable Available Now'"></span>
                                                </button>
                                            </div>
                                        @endif

                                        {{-- Edit / Switch / Delete buttons always on one row --}}
                                        <div class="flex flex-row flex-wrap items-center gap-2">
                                            <form method="POST" action="{{ route('profiles.switch-edit', $profile) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-center text-sm font-medium text-gray-700 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-700"
                                                >
                                                    Edit
                                                </button>
                                            </form>

                                            @if((int)$activeProfileId !== $profile->id)
                                                <form method="POST" action="{{ route('profiles.switch', $profile) }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="rounded-lg bg-pink-600 px-3 py-1.5 text-center text-sm font-medium text-white transition hover:bg-pink-700"
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
                                                    class="rounded-lg bg-rose-50 px-3 py-1.5 text-center text-sm font-medium text-rose-700 transition hover:bg-rose-100"
                                                    aria-label="Delete profile {{ $profile->name }}"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Visibility notice for non-approved profiles --}}
                                @if($profile->profile_status !== 'approved')
                                    <div class="mt-2 rounded-lg border
                                        @if($profile->profile_status === 'rejected') border-red-200 bg-red-50 text-red-700
                                        @else border-yellow-200 bg-yellow-50 text-yellow-700
                                        @endif
                                        px-3 py-2 text-xs font-medium"
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
                                            ? 'border-green-200 bg-green-50 text-green-700'
                                            : 'border-red-200 bg-red-50 text-red-700'"
                                        x-text="message"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <button
                    type="button"
                    @click="showCreateModal = true; createName = ''; createPhone = ''; createErrors = []"
                    class="inline-flex w-full items-center justify-center rounded-full border border-transparent bg-pink-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                >
                    + Create New Profile
                </button>

                <a
                    href="{{ route('account.delete-page') }}"
                    class="mt-3 inline-flex w-full items-center justify-center rounded-full border border-rose-200 bg-rose-50 px-6 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 sm:mt-0 sm:ml-2 sm:w-auto"
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
                <div
                    class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
                    @click.stop
                >
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Create New Profile</h2>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                            aria-label="Close modal"
                        >&times;</button>
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
                            if (!createName.trim()) { createErrors.push('Profile name is required.'); return; }
                            $el.submit();
                        "
                        class="space-y-4"
                    >
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-[#e04ecb] mb-1">Profile name <span class="text-red-500">*</span></label>
                            <input
                                name="name"
                                type="text"
                                x-model="createName"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-[#e04ecb] focus:outline-none focus:ring-2 focus:ring-[#e04ecb]/30"
                                placeholder="e.g. Jenny"
                                required
                                autofocus
                            >
                            <p class="mt-1 text-xs text-gray-500">This is the name that will appear on the listing (e.g. Jenny, Kate).</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#e04ecb] mb-1">Mobile number <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input
                                name="phone"
                                type="text"
                                x-model="createPhone"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-[#e04ecb] focus:outline-none focus:ring-2 focus:ring-[#e04ecb]/30"
                                placeholder="e.g. 0400 000 000"
                            >
                            <p class="mt-1 text-xs text-gray-500">You can set or change this later.</p>
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
            // ─── Account deletion ─────────────────────────────────────────────
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
                // ─── Individual profile deletion ──────────────────────────────
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

                @if($profiles->count() > 1)
                    // ─── Delete selected profiles ──────────────────────────────────
                    const deleteSelectedForm = document.getElementById('delete-selected-profiles-form');
                    if (deleteSelectedForm) {
                        deleteSelectedForm.addEventListener('submit', async function (e) {
                            e.preventDefault();
                            const result = await Swal.fire({
                                title: 'Delete selected profiles?',
                                text: 'This cannot be undone.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Delete',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#dc2626',
                            });
                            if (result.isConfirmed) {
                                deleteSelectedForm.submit();
                            }
                        });
                    }
                @endif

                // ─── Flash messages ────────────────────────────────────────────
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
