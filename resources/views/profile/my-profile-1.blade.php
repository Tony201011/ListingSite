@extends('layouts.frontend')

@section('content')
@php
    $statusSettings = \App\Models\SiteSetting::getStatusSettings();
@endphp
<div
    class="bg-white min-h-screen py-10 px-4"
    x-data="{ availableNow: false, onlineNow: false }"
>
    <main class="max-w-7xl mx-auto px-6 py-8">
        <div class="bg-white min-h-[600px]">
        <button
            type="button"
            onclick="window.history.back()"
            class="inline-flex items-center text-pink-500 hover:text-pink-600 transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="text-3xl font-bold mb-8 text-gray-900">
            My Profile
        </h1>

        <div class="mb-6 border border-gray-300 rounded-lg p-6">
                <div x-data="{ showSuccess: true }">
                    @if(session('success'))
                        <div
                            x-show="showSuccess"
                            x-transition
                            class="mb-4 flex items-start justify-between gap-3 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                        >
                            <span>{{ session('success') }}</span>

                            <button
                                type="button"
                                @click="showSuccess = false"
                                class="text-lg leading-none text-green-500 hover:text-green-700"
                            >
                                &times;
                            </button>
                        </div>
                    @endif
                </div>

                <div x-data="{ showError: true }">
                    @if(session('error'))
                        <div
                            x-show="showError"
                            x-transition
                            class="mb-4 flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
                        >
                            <span>{{ session('error') }}</span>

                            <button
                                type="button"
                                @click="showError = false"
                                class="text-lg leading-none text-red-500 hover:text-red-700"
                            >
                                &times;
                            </button>
                        </div>
                    @endif
                </div>

                <p class="mb-6 text-lg font-medium text-gray-600">
                    To set up your profile please do the next three steps:
                </p>

                @if($profile)
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-pink-100 bg-pink-50 px-4 py-3">
                        <div class="flex items-center gap-2 text-sm font-medium text-pink-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Active profile: <strong>{{ $profile->name }}</strong>
                                @if($profile->slug)
                                    <span class="ml-1 text-xs text-pink-500">({{ $profile->slug }})</span>
                                @endif
                            </span>
                        </div>
                        <a
                            href="{{ route('profiles.index') }}"
                            class="text-sm font-medium text-pink-600 underline-offset-2 hover:underline"
                        >
                            Manage profiles
                        </a>
                    </div>
                @else
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-pink-100 bg-pink-50 px-4 py-3">
                        <span class="text-sm font-medium text-pink-800">No profile yet — complete the steps below to create one.</span>
                        <a
                            href="{{ route('profiles.index') }}"
                            class="text-sm font-medium text-pink-600 underline-offset-2 hover:underline"
                        >
                            Manage profiles
                        </a>
                    </div>
                @endif

                @php
                    $activeStep = ! $stepOneCompleted
                        ? 1
                        : (! $stepTwoCompleted
                            ? 2
                            : (! $stepPhotoVerificationCompleted ? 3 : null));
                @endphp

                <div class="mb-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-200 py-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Action</span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Completed</span>
                    </div>

                    <div class="rounded-lg border px-4 py-4 transition-all duration-300 {{ $activeStep === 1 ? 'border-pink-300 bg-pink-50' : ($stepOneCompleted ? 'border-green-200 bg-green-50/70' : 'border-gray-300 bg-white') }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="mr-4 text-lg font-semibold {{ $activeStep === 1 ? 'text-pink-600' : ($stepOneCompleted ? 'text-green-600' : 'text-gray-400') }}">01</span>
                                <span class="font-medium {{ $activeStep === 1 ? 'text-pink-800' : ($stepOneCompleted ? 'text-green-700' : 'text-gray-500') }}">Write profile text</span>
                            </div>

                            @if($stepOneCompleted)
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-sm text-white">✓</span>
                            @elseif($activeStep === 1)
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border-2 border-pink-500 bg-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-pink-500"></span>
                                </span>
                            @else
                                <span class="inline-block h-6 w-6 rounded-full border-2 border-gray-300"></span>
                            @endif
                        </div>

                        @if($activeStep === 1)
                            <a
                                href="{{ route('edit-profile') }}"
                                class="mt-4 inline-flex w-full items-center justify-center rounded bg-pink-500 px-6 py-3 text-base font-medium text-white transition hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                            >
                                Write profile text
                            </a>
                        @endif
                    </div>

                    <div class="rounded-lg border px-4 py-4 transition-all duration-300 {{ $activeStep === 2 ? 'border-pink-300 bg-pink-50' : ($stepTwoCompleted ? 'border-green-200 bg-green-50/70' : 'border-gray-300 bg-white') }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="mr-4 text-lg font-semibold {{ $activeStep === 2 ? 'text-pink-600' : ($stepTwoCompleted ? 'text-green-600' : 'text-gray-400') }}">02</span>
                                <span class="font-medium {{ $activeStep === 2 ? 'text-pink-800' : ($stepTwoCompleted ? 'text-green-700' : 'text-gray-500') }}">Upload photos</span>
                            </div>

                            @if($stepTwoCompleted)
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-sm text-white">✓</span>
                            @elseif($activeStep === 2)
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border-2 border-pink-500 bg-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-pink-500"></span>
                                </span>
                            @else
                                <span class="inline-block h-6 w-6 rounded-full border-2 border-gray-300"></span>
                            @endif
                        </div>

                        @if($activeStep === 2)
                            <a
                                href="{{ route('photos') }}"
                                class="mt-4 inline-flex w-full items-center justify-center rounded bg-pink-500 px-6 py-3 text-base font-medium text-white transition hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                            >
                                Upload photos
                            </a>
                        @endif
                    </div>

                    <div class="rounded-lg border px-4 py-4 transition-all duration-300 {{ $activeStep === 3 ? 'border-pink-300 bg-pink-50' : ($stepPhotoVerificationCompleted ? 'border-green-200 bg-green-50/70' : 'border-gray-300 bg-white') }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="mr-4 text-lg font-semibold {{ $activeStep === 3 ? 'text-pink-600' : ($stepPhotoVerificationCompleted ? 'text-green-600' : 'text-gray-400') }}">03</span>
                                <span class="font-medium {{ $activeStep === 3 ? 'text-pink-800' : ($stepPhotoVerificationCompleted ? 'text-green-700' : 'text-gray-500') }}">Verify your photos (optional)</span>
                            </div>

                            @if($stepPhotoVerificationCompleted)
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-sm text-white">✓</span>
                            @elseif($activeStep === 3)
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border-2 border-pink-500 bg-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-pink-500"></span>
                                </span>
                            @else
                                <span class="inline-block h-6 w-6 rounded-full border-2 border-gray-300"></span>
                            @endif
                        </div>

                        @if($activeStep === 3)
                            <a
                                href="{{ url('/verify-photo') }}"
                                class="mt-4 inline-flex w-full items-center justify-center rounded bg-pink-500 px-6 py-3 text-base font-medium text-white transition hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                            >
                                Verify photos
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    @if($stepOneCompleted)
                        <a href="{{ route('edit-profile') }}" class="transition hover:text-gray-700">Edit profile text</a>
                    @endif
                    @if($stepTwoCompleted)
                        <a href="{{ route('photos') }}" class="transition hover:text-gray-700">Manage photos</a>
                    @endif
                    @if($stepPhotoVerificationCompleted)
                        <a href="{{ url('/verify-photo') }}" class="transition hover:text-gray-700">Edit verified photos</a>
                    @endif
                </div>

                <div class="mt-6 rounded-lg border-l-4 border-[#e04ecb] bg-pink-50 p-4 text-sm font-semibold text-pink-700 sm:text-base">
                    You can list your profile without photo verification.
                    <span class="font-bold">
                        If you verify photos, you receive a “Photos Verified” badge for extra trust.
                    </span>
                </div>
        </div>

        @if($stepOneCompleted && $stepTwoCompleted)
            <div class="mb-4 rounded-lg bg-[#e04ecb] p-6 text-white">
                @if(!$stepPhotoVerificationCompleted)
                    <h2 class="mb-2 flex items-center gap-2 text-xl font-bold">
                        ✅ PHOTO VERIFICATION OPTIONAL
                    </h2>
                @endif

                <p class="mb-4 text-sm sm:text-base">
                    Your profile can be listed with or without verification. Verify 2 photos only if you want the “Photos Verified” badge.
                </p>

                @if($stepPhotoVerificationCompleted)
                    <a
                        href="{{ url('/verify-photo') }}"
                        class="inline-flex rounded-lg bg-white px-5 py-2 font-medium text-gray-700 transition hover:bg-pink-50"
                    >
                        Edit photos for badge
                    </a>
                @else
                    <a
                        href="{{ url('/verify-photo') }}"
                        class="inline-flex rounded-lg bg-white px-5 py-2 font-medium text-gray-700 transition hover:bg-pink-50"
                    >
                        Verify photos for badge
                    </a>
                @endif

                <p class="mt-4 text-sm text-white/90">
                    If you already submitted photos by email or SMS, just wait for the badge review.
                </p>
            </div>

            <div class="mb-6 text-right">
                <a
                    href="{{ url('/profile-setting') }}"
                    class="inline-flex items-center justify-center rounded bg-pink-500 px-6 py-2.5 font-semibold text-white transition hover:bg-pink-600"
                >
                    View your profile & settings
                </a>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="border border-gray-300 rounded-lg p-6">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">CREDITS</h3>
                    <p class="mb-3 text-3xl font-bold text-gray-900">
                        {{ $profile?->credits ?? 0 }} <span class="text-base font-normal text-gray-500">credits available</span>
                    </p>
                    <div class="space-y-2">
                        <a href="{{ url('/purchase-credit') }}" class="block w-full rounded bg-pink-500 px-4 py-2 text-center text-white transition hover:bg-pink-600">Add balance</a>
                        <a href="{{ url('/credit-history') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Credits history</a>
                        <a href="{{ route('profile-spending-history') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Profile spending history</a>
                        <a href="{{ url('/purchase-history') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Purchase history</a>
                        <a href="{{ url('/membership') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Membership plans</a>
                        <a href="{{ route('activity-logs') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Activity logs</a>
                    </div>
                </div>

                <div class="border border-gray-300 rounded-lg p-6 md:col-span-2 xl:col-span-1">
                    <h3 class="mb-3 text-lg font-bold text-gray-800">LISTING BOOST STATUS</h3>
                    <dl class="space-y-2 text-sm text-gray-700">
                        @foreach (($listingBoostStatuses ?? []) as $status)
                            <div class="flex items-start justify-between gap-3">
                                <dt class="font-medium text-gray-600">{{ $status['label'] }}</dt>
                                <dd class="text-right">{{ $status['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <a href="{{ route('featured') }}" class="mt-4 block w-full rounded bg-pink-500 px-4 py-2 text-center text-white transition hover:bg-pink-600">
                        Set & purchase boosts
                    </a>
                </div>

                <div class="border border-gray-300 rounded-lg p-6">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">BABE RANK</h3>
                    <p class="mb-3 text-3xl font-bold text-gray-900">
                        {{ $babeRank }} <span class="text-base font-normal text-gray-500">out of 100</span>
                    </p>
                    <a href="{{ url('/babe-rank-read-more') }}" class="text-sm font-medium text-pink-600 hover:text-pink-700">
                        Read more about BabeRank
                    </a>
                    <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-gray-600">
                        <li>Set your short URL</li>
                        <li>Set your availability</li>
                        <li>Upload new photos</li>
                        <li>Update your profile text</li>
                        <li>Upload videos</li>
                    </ul>
                </div>

                <div class="border border-gray-300 rounded-lg p-6">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">YOUR RATES</h3>
                    <p class="mb-2 text-sm font-medium text-pink-600">13 May 2022:</p>
                    <p class="mb-4 text-sm text-gray-600">
                        With this feature you can easily add your rates and choose how they appear on your profile.
                    </p>
                    <a href="{{ url('/my-rate') }}" class="block w-full rounded bg-pink-500 px-4 py-2 text-center text-white transition hover:bg-pink-600">
                        NEW Configure your rates
                    </a>
                </div>

                <div class="border border-gray-300 rounded-lg p-6">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">YOUR AVAILABILITY</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        You have not set your availability. This gives your BabeRank a boost of 70%.
                    </p>
                    <button
                        type="button"
                        @click="window.location.href='{{ route('availability.show') }}'"
                        class="w-full rounded bg-pink-500 px-4 py-2 text-white transition hover:bg-pink-600"
                    >
                        Set availability
                    </button>
                </div>

                <div class="border border-gray-300 rounded-lg p-6">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">AVAILABLE NOW</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Promote your availability twice a day for two hours.
                    </p>
                    <button
                        type="button"
                        @click="window.location.href='{{ route('available-now') }}'"
                        class="w-full rounded bg-pink-500 px-4 py-2 text-white transition hover:bg-pink-600"
                    >
                        Set Available Now
                    </button>
                </div>

                <div class="border border-gray-300 rounded-lg p-6">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">ONLINE NOW</h3>

                    <p class="mb-4 text-sm text-gray-600">
                        Mark yourself available for online enquiries and improve visibility.
                    </p>

                    <button
                        type="button"
                        @click="window.location.href='{{ route('online-now') }}'"
                        class="w-full rounded bg-pink-500 px-4 py-2 text-white transition hover:bg-pink-600"
                    >
                        Set Online NOW
                    </button>
                </div>

                <div class="border border-gray-300 rounded-lg p-6 md:col-span-2 xl:col-span-1">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">Referral Code</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Share your referral code with friends and earn rewards.
                    </p>

                    @php
                        $referralCode = $profile->account_user_referral_code ?? 'dsgfdgfdgfdgfdg';
                        $referralLink = url('/signup?ref=' . $referralCode);
                    @endphp

                    <div
                        class="space-y-3"
                        x-data="referralCopy({
                            code: @js($referralCode),
                            link: @js($referralLink)
                        })"
                    >
                        <div class="flex items-center justify-between rounded bg-gray-100 px-4 py-2">
                            <span class="font-medium text-gray-800" x-text="code"></span>
                            <button
                                type="button"
                                @click="copyCode()"
                                class="text-sm text-blue-600 hover:underline"
                                x-text="buttonText"
                            ></button>
                        </div>

                        <button
                            type="button"
                            @click="copyLink()"
                            class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200"
                        >
                            Copy Referral Link
                        </button>

                        <a
                            href="{{ url('/referral') }}"
                            class="block w-full rounded bg-blue-50 px-4 py-2 text-center text-blue-700 transition hover:bg-blue-100"
                        >
                            View Referrals
                        </a>
                    </div>
                </div>

                <div class="border border-gray-300 rounded-lg p-6 md:col-span-2 xl:col-span-1">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">ACCOUNT SECURITY</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Manage your password and account access settings.
                    </p>

                    @if($stepOneCompleted && $stepTwoCompleted)
                        <div class="mb-4 flex items-center gap-2 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Profile complete
                        </div>
                    @else
                        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                            <div class="mb-1 flex items-center gap-2 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Profile incomplete
                            </div>
                            <ul class="ml-6 list-disc space-y-0.5 text-xs">
                                @if(!$stepOneCompleted)
                                    <li><a href="{{ route('edit-profile') }}" class="underline underline-offset-2 hover:text-amber-900">Write profile text</a> to unlock account settings</li>
                                @endif
                                @if(!$stepTwoCompleted)
                                    <li><a href="{{ route('photos') }}" class="underline underline-offset-2 hover:text-amber-900">Upload at least one photo</a> to unlock account settings</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-2">
                        @auth
                            @if (!auth()->user()->hasVerifiedEmail())
                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="block w-full rounded bg-blue-50 px-4 py-2 text-center text-blue-700 transition hover:bg-blue-100"
                                    >
                                        Verify email
                                    </button>
                                </form>
                            @endif
                        @endauth

                        <a href="{{ url('/change-password') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Change password</a>
                        <a href="{{ url('/change-email') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Change email</a>
                    </div>
                </div>
            </div>

            <div class="mt-8 border border-gray-300 rounded-lg p-6">
                <p class="mb-2 font-medium text-gray-700">
                    You can be found on Hotescorts with the following URLs
                </p>
                @if($profileUrl)
                    <a href="{{ $profileUrl }}" target="_blank" class="block break-all font-semibold text-pink-600 hover:underline">
                        {{ $profileUrl }}
                    </a>
                @endif
                @if($shortUrlFull)
                    <a href="{{ $shortUrlFull }}" target="_blank" class="mt-1 block break-all font-semibold text-pink-600 hover:underline">
                        {{ $shortUrlFull }}
                    </a>
                @endif
            </div>
        @endif
        @if(!$stepOneCompleted && !$stepTwoCompleted)
                <div class="border border-gray-300 rounded-lg p-6 md:col-span-2 xl:col-span-1">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">ACCOUNT SECURITY</h3>

                    <div class="space-y-2">
                        <a href="{{ url('/change-password') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Change password</a>
                        <a href="{{ url('/change-email') }}" class="block w-full rounded bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Change email</a>
                    </div>
                </div>
        @endif
        </div>
    </main>
</div>


@endsection

@push('scripts')

<script src="{{ asset('profile/js/referral-copy.js') }}"></script>
@endpush
