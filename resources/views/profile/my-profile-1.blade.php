@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="{ availableNow: false, onlineNow: false }"
>
    <div class="mx-auto max-w-6xl">
        <button
            type="button"
            onclick="window.history.back()"
            class="mb-4 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-[#e04ecb] transition-colors hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="mb-8 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
            Hotescorts dashboard
        </h1>

        <div class="mb-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="p-6 sm:p-8">
                <div x-data="{ showSuccess: true }">
                    @if(session('success'))
                        <div
                            x-show="showSuccess"
                            x-transition
                            class="mb-4 flex items-start justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
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

                <div class="mb-6 space-y-1">
                    <div class="flex items-center justify-between border-b border-gray-200 py-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Action</span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Completed</span>
                    </div>

                    <div class="-mx-2 flex items-center justify-between rounded-lg px-2 py-4 transition hover:bg-gray-50">
                        <div class="flex items-center">
                            <span class="mr-4 text-lg font-semibold text-pink-600">01</span>
                            <span class="font-medium text-gray-800">Write profile text</span>
                        </div>

                        @if($stepOneCompleted)
                            <span class="text-2xl leading-none text-green-500">✓</span>
                        @else
                            <span class="inline-block h-6 w-6 rounded-full border-2 border-gray-300"></span>
                        @endif
                    </div>

                    <div class="-mx-2 flex items-center justify-between rounded-lg px-2 py-4 transition hover:bg-gray-50">
                        <div class="flex items-center">
                            <span class="mr-4 text-lg font-semibold text-pink-600">02</span>
                            <span class="font-medium text-gray-800">Upload photos</span>
                        </div>

                        @if($stepTwoCompleted)
                            <span class="text-2xl leading-none text-green-500">✓</span>
                        @else
                            <span class="inline-block h-6 w-6 rounded-full border-2 border-gray-300"></span>
                        @endif
                    </div>

                    <div class="-mx-2 flex items-center justify-between rounded-lg px-2 py-4 transition hover:bg-gray-50">
                        <div class="flex items-center">
                            <span class="mr-4 text-lg font-semibold text-pink-600">03</span>
                            <span class="font-medium text-gray-800">Verify your photos (optional)</span>
                        </div>

                        @if($stepPhotoVerificationCompleted)
                            <span class="text-2xl leading-none text-green-500">✓</span>
                        @else
                            <span class="inline-block h-6 w-6 rounded-full border-2 border-gray-300"></span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    @if($stepOneCompleted && $stepTwoCompleted)

                        @if($stepPhotoVerificationCompleted)
                            <a
                                href="{{ url('/verify-photo') }}"
                                class="text-sm text-gray-500 transition hover:text-gray-700"
                            >
                                edit your verified photos
                            </a>
                        @else
                            <a
                                href="{{ url('/verify-photo') }}"
                                class="inline-flex w-full transform items-center justify-center rounded-full border border-transparent bg-pink-600 px-8 py-3.5 text-base font-medium text-white shadow-lg shadow-pink-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                            >
                                Verified Photos Badges
                            </a>
                        @endif

                        <a
                            href="{{ route('edit-profile') }}"
                            class="text-sm text-gray-500 transition hover:text-gray-700"
                        >
                            or edit your profile text
                        </a>

                        <a
                            href="{{ route('photos') }}"
                            class="text-sm text-gray-500 transition hover:text-gray-700"
                        >
                            or upload more photos
                        </a>

                    @elseif($stepOneCompleted)
                        <a
                            href="{{ route('photos') }}"
                            class="inline-flex w-full transform items-center justify-center rounded-full border border-transparent bg-pink-600 px-8 py-3.5 text-base font-medium text-white shadow-lg shadow-pink-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Upload photos
                        </a>

                        <a
                            href="{{ route('edit-profile') }}"
                            class="text-sm text-gray-500 transition hover:text-gray-700"
                        >
                            or edit your profile text
                        </a>

                    @elseif($stepTwoCompleted)
                        <a
                            href="{{ route('edit-profile') }}"
                            class="inline-flex w-full transform items-center justify-center rounded-full border border-transparent bg-pink-600 px-8 py-3.5 text-base font-medium text-white shadow-lg shadow-pink-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Complete profile text
                        </a>

                        <a
                            href="{{ route('photos') }}"
                            class="text-sm text-gray-500 transition hover:text-gray-700"
                        >
                            or manage photos
                        </a>

                    @elseif(!$stepOneCompleted && !$stepTwoCompleted && !$stepPhotoVerificationCompleted)
                        <a
                            href="{{ route('edit-profile') }}"
                            class="inline-flex w-full transform items-center justify-center rounded-full border border-transparent bg-pink-600 px-8 py-3.5 text-base font-medium text-white shadow-lg shadow-pink-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Write profile text
                        </a>
                    @endif
                </div>

                <div class="mt-6 rounded-xl border-l-4 border-[#e04ecb] bg-pink-50 p-4 text-sm font-semibold text-pink-700 sm:text-base">
                    You can list your profile without photo verification.
                    <span class="font-bold">
                        If you verify photos, you receive a “Photos Verified” badge for extra trust.
                    </span>
                </div>
            </div>
        </div>

        @if($stepOneCompleted && $stepTwoCompleted)
            <div class="mb-4 rounded-2xl bg-[#e04ecb] p-5 text-white shadow-sm sm:p-6">
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
                    class="inline-flex items-center justify-center rounded-full bg-pink-600 px-6 py-2.5 font-semibold text-white transition hover:bg-pink-700"
                >
                    View your profile & settings
                </a>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">CREDITS</h3>
                    <p class="mb-3 text-3xl font-bold text-gray-900">
                        21 <span class="text-base font-normal text-gray-500">credits available</span>
                    </p>
                    <div class="space-y-2">
                        <a href="{{ url('/purchase-credit') }}" class="block w-full rounded-lg bg-pink-600 px-4 py-2 text-center text-white transition hover:bg-pink-700">Purchase credits</a>
                        <a href="{{ url('/credit-history') }}" class="block w-full rounded-lg bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Credits history</a>
                        <a href="{{ url('/purchase-history') }}" class="block w-full rounded-lg bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Purchase history</a>
                        <a href="{{ url('/membership') }}" class="block w-full rounded-lg bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Membership plans</a>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
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

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">YOUR RATES</h3>
                    <p class="mb-2 text-sm font-medium text-pink-600">13 May 2022:</p>
                    <p class="mb-4 text-sm text-gray-600">
                        With this feature you can easily add your rates and choose how they appear on your profile.
                    </p>
                    <a href="{{ url('/my-rate') }}" class="block w-full rounded-lg bg-pink-600 px-4 py-2 text-center text-white transition hover:bg-pink-700">
                        NEW Configure your rates
                    </a>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">YOUR AVAILABILITY</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        You have not set your availability. This gives your BabeRank a boost of 70%.
                    </p>
                    <button
                        type="button"
                        @click="window.location.href='{{ route('availability.show') }}'"
                        class="w-full rounded-lg bg-pink-600 px-4 py-2 text-white transition hover:bg-pink-700"
                    >
                        Set availability
                    </button>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">AVAILABLE NOW</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Promote your availability twice a day for two hours.
                    </p>
                    <button
                        type="button"
                        @click="window.location.href='{{ route('available-now') }}'"
                        class="w-full rounded-lg bg-pink-600 px-4 py-2 text-white transition hover:bg-pink-700"
                    >
                        Set Available Now
                    </button>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">ONLINE NOW</h3>

                    <p class="mb-4 text-sm text-gray-600">
                        Use this feature up to 4 times a day for 60 minutes.
                    </p>

                    <button
                        type="button"
                        @click="window.location.href='{{ route('online-now') }}'"
                        class="w-full rounded-lg bg-pink-600 px-4 py-2 text-white transition hover:bg-pink-700"
                    >
                        Set Online NOW
                    </button>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm md:col-span-2 xl:col-span-1">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">Referral Code</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Share your referral code with friends and earn rewards.
                    </p>

                    @php
                        $referralCode = $profile->account_user_referral_code ?? 'dsgfdgfdgfdgfdg';
                        $referralLink = url('/register?ref=' . $referralCode);
                    @endphp

                    <div
                        class="space-y-3"
                        x-data="referralCopy({
                            code: @js($referralCode),
                            link: @js($referralLink)
                        })"
                    >
                        <div class="flex items-center justify-between rounded-lg bg-gray-100 px-4 py-2">
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
                            class="block w-full rounded-lg bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200"
                        >
                            Copy Referral Link
                        </button>

                        <a
                            href="{{ url('/referral') }}"
                            class="block w-full rounded-lg bg-blue-50 px-4 py-2 text-center text-blue-700 transition hover:bg-blue-100"
                        >
                            View Referrals
                        </a>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm md:col-span-2 xl:col-span-1">
                    <h3 class="mb-2 text-lg font-bold text-gray-800">ACCOUNT SECURITY</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Manage your password and account access settings.
                    </p>
                    <div class="space-y-2">
                        @auth
                            @if (!auth()->user()->hasVerifiedEmail())
                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="block w-full rounded-lg bg-blue-50 px-4 py-2 text-center text-blue-700 transition hover:bg-blue-100"
                                    >
                                        Verify email
                                    </button>
                                </form>
                            @endif
                        @endauth

                        <a href="{{ url('/change-password') }}" class="block w-full rounded-lg bg-gray-100 px-4 py-2 text-center text-gray-700 transition hover:bg-gray-200">Change password</a>
                        <a href="{{ url('/delete-account') }}" class="block w-full rounded-lg bg-rose-50 px-4 py-2 text-center text-rose-700 transition hover:bg-rose-100">Delete account</a>
                    </div>
                </div>
            </div>

            <div class="mt-8 rounded-xl border border-gray-100 bg-white p-5 sm:p-6">
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
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/referral-copy.js') }}"></script>
@endpush
