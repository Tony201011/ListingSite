@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="{ availableNow: false, onlineNow: false }"
>
    <div class="max-w-6xl mx-auto">
        <button
            type="button"
            onclick="window.history.back()"
            class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium bg-transparent border-0 cursor-pointer"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-8 tracking-tight">
            Hotescorts dashboard
        </h1>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 sm:p-8">
                <div x-data="{ showError: true }">
                @if(session('error'))
                    <div
                        x-show="showError"
                        x-transition
                        class="mb-4 flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 font-medium"
                    >
                        <span>{{ session('error') }}</span>

                        <button
                            type="button"
                            @click="showError = false"
                            class="text-red-500 hover:text-red-700 text-lg leading-none"
                        >
                            &times;
                        </button>
                    </div>
                @endif
        </div>
                <p class="text-lg text-gray-600 mb-6 font-medium">
                    To set up your profile please do the next three steps:
                </p>

                <div class="space-y-1 mb-6">
                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</span>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Completed</span>
                    </div>

                    <div class="flex items-center justify-between py-4 px-2 -mx-2 rounded-lg hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <span class="text-lg font-semibold text-pink-600 mr-4">01</span>
                            <span class="text-gray-800 font-medium">Write profile text</span>
                        </div>

                        @if($stepOneCompleted)
                            <span class="text-green-500 text-2xl leading-none">✓</span>
                        @else
                            <span class="w-6 h-6 rounded-full border-2 border-gray-300 inline-block"></span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between py-4 px-2 -mx-2 rounded-lg hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <span class="text-lg font-semibold text-pink-600 mr-4">02</span>
                            <span class="text-gray-800 font-medium">Upload photos</span>
                        </div>

                        @if($stepTwoCompleted)
                            <span class="text-green-500 text-2xl leading-none">✓</span>
                        @else
                            <span class="w-6 h-6 rounded-full border-2 border-gray-300 inline-block"></span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between py-4 px-2 -mx-2 rounded-lg hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <span class="text-lg font-semibold text-pink-600 mr-4">03</span>
                            <span class="text-gray-800 font-medium">Verify your photos (optional)</span>
                        </div>

                        @if($stepPhotoVerificationCompleted)
                            <span class="text-green-500 text-2xl leading-none">✓</span>
                        @else
                            <span class="w-6 h-6 rounded-full border-2 border-gray-300 inline-block"></span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    @if($stepOneCompleted && $stepTwoCompleted)

                        @if($stepPhotoVerificationCompleted)
                            <a
                                href="{{ url('/click-here-to-verify') }}"
                                class="text-sm text-gray-500 hover:text-gray-700 transition"
                            >
                                edit your verified photos
                            </a>
                        @else
                            <a
                                href="{{ url('/click-here-to-verify') }}"
                                class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5"
                            >
                                Verified Photos Badges
                            </a>
                        @endif

                        <a
                            href="{{ route('edit-profile') }}"
                            class="text-sm text-gray-500 hover:text-gray-700 transition"
                        >
                            or edit your profile text
                        </a>

                        <a
                            href="{{ route('photos') }}"
                            class="text-sm text-gray-500 hover:text-gray-700 transition"
                        >
                            or upload more photos
                        </a>

                    @elseif($stepOneCompleted)
                        <a
                            href="{{ route('photos') }}"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5"
                        >
                            Upload photos
                        </a>

                        <a
                            href="{{ route('edit-profile') }}"
                            class="text-sm text-gray-500 hover:text-gray-700 transition"
                        >
                            or edit your profile text
                        </a>

                    @elseif($stepTwoCompleted)
                        <a
                            href="{{ route('edit-profile') }}"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5"
                        >
                            Complete profile text
                        </a>

                        <a
                            href="{{ route('photos') }}"
                            class="text-sm text-gray-500 hover:text-gray-700 transition"
                        >
                            or manage photos
                        </a>

                    @elseif(!$stepOneCompleted && !$stepTwoCompleted && !$stepPhotoVerificationCompleted)
                        <a
                            href="{{ route('edit-profile') }}"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5"
                        >
                            Write profile text
                        </a>
                    @endif
                </div>

                <div class="mt-6 bg-pink-50 border-l-4 border-[#e04ecb] rounded-xl p-4 text-pink-700 font-semibold text-sm sm:text-base">
                    You can list your profile without photo verification.
                    <span class="font-bold">
                        If you verify photos, you receive a “Photos Verified” badge for extra trust.
                    </span>
                </div>
            </div>
        </div>

        @if($stepOneCompleted && $stepTwoCompleted)
            <div class="bg-[#e04ecb] text-white rounded-2xl p-5 sm:p-6 mb-4 shadow-sm">
                @if(!$stepPhotoVerificationCompleted)
                    <h2 class="text-xl font-bold mb-2 flex items-center gap-2">
                        ✅ PHOTO VERIFICATION OPTIONAL
                    </h2>
                @endif

                <p class="mb-4 text-sm sm:text-base">
                    Your profile can be listed with or without verification. Verify 2 photos only if you want the “Photos Verified” badge.
                </p>

                @if($stepPhotoVerificationCompleted)
                    <a
                        href="{{ url('/click-here-to-verify') }}"
                        class="inline-flex bg-white text-gray-700 hover:bg-pink-50 px-5 py-2 rounded-lg font-medium transition"
                    >
                        Edit photos for badge
                    </a>
                @else
                    <a
                        href="{{ url('/click-here-to-verify') }}"
                        class="inline-flex bg-white text-gray-700 hover:bg-pink-50 px-5 py-2 rounded-lg font-medium transition"
                    >
                        Verify photos for badge
                    </a>
                @endif

                <p class="mt-4 text-sm text-white/90">
                    If you already submitted photos by email or SMS, just wait for the badge review.
                </p>
            </div>

            <div class="text-right mb-6">
                <a
                    href="{{ url('/view-profile-setting') }}"
                    class="inline-flex items-center justify-center px-6 py-2.5 rounded-full bg-pink-600 hover:bg-pink-700 text-white font-semibold transition"
                >
                    View your profile & settings
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">CREDITS</h3>
                    <p class="text-3xl font-bold text-gray-900 mb-3">
                        21 <span class="text-base font-normal text-gray-500">credits available</span>
                    </p>
                    <div class="space-y-2">
                        <a href="{{ url('/purchase-credit') }}" class="block w-full px-4 py-2 rounded-lg bg-pink-600 text-white hover:bg-pink-700 transition text-center">Purchase credits</a>
                        <a href="{{ url('/credit-history') }}" class="block w-full px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition text-center">Credits history</a>
                        <a href="{{ url('/purchase-history') }}" class="block w-full px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition text-center">Purchase history</a>
                        <a href="{{ url('/membership') }}" class="block w-full px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition text-center">Membership plans</a>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">BABE RANK</h3>
                    <p class="text-3xl font-bold text-gray-900 mb-3">
                        7 <span class="text-base font-normal text-gray-500">out of 100</span>
                    </p>
                    <a href="{{ url('/babe-rank-read-more') }}" class="text-pink-600 font-medium hover:text-pink-700 text-sm">
                        Read more about BabeRank
                    </a>
                    <ul class="mt-3 text-sm text-gray-600 list-disc list-inside space-y-1">
                        <li>Set your short URL</li>
                        <li>Set your availability</li>
                        <li>Upload new photos</li>
                        <li>Update your profile text</li>
                        <li>Upload videos</li>
                    </ul>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">YOUR RATES</h3>
                    <p class="text-pink-600 font-medium text-sm mb-2">13 May 2022:</p>
                    <p class="text-sm text-gray-600 mb-4">
                        With this feature you can easily add your rates and choose how they appear on your profile.
                    </p>
                    <a href="{{ url('/my-rate') }}" class="block w-full px-4 py-2 rounded-lg bg-pink-600 text-white hover:bg-pink-700 transition text-center">
                        NEW Configure your rates
                    </a>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">YOUR AVAILABILITY</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        You have not set your availability. This gives your BabeRank a boost of 70%.
                    </p>
                    <a href="{{ url('/my-availability') }}" class="block w-full px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition text-center">
                        Set availability
                    </a>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">AVAILABLE NOW</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Promote your availability twice a day for two hours.
                    </p>
                    <button
                        type="button"
                        x-on:click="availableNow = !availableNow"
                        class="w-full px-4 py-2 rounded-lg transition"
                        :class="availableNow ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        x-text="availableNow ? 'Enabled' : 'Available NOW'"
                    ></button>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">ONLINE NOW</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Use this feature up to 4 times a day for 60 minutes.
                    </p>
                    <button
                        type="button"
                        x-on:click="onlineNow = !onlineNow"
                        class="w-full px-4 py-2 rounded-lg transition"
                        :class="onlineNow ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        x-text="onlineNow ? 'Enabled' : 'Online NOW'"
                    ></button>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm md:col-span-2 xl:col-span-1">
    <h3 class="text-lg font-bold text-gray-800 mb-2">Referral Code</h3>
    <p class="text-sm text-gray-600 mb-4">
        Share your referral code with friends and earn rewards.
    </p>

    @php
        $referralCode = $profile->account_user_referral_code ?? 'dsgfdgfdgfdgfdg';
        $referralLink = url('/register?ref=' . $referralCode);
    @endphp

    <div class="space-y-3">
        <!-- Code + Copy -->
        <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded-lg">
            <span id="referralCode" class="text-gray-800 font-medium">{{ $referralCode }}</span>
            <button
                id="copyBtn"
                type="button"
                onclick="copyReferralCode()"
                class="text-sm text-blue-600 hover:underline"
            >
                Copy
            </button>
        </div>


        <!-- View Referrals -->
        <a href="{{ url('/referrals') }}"
           class="block w-full px-4 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition text-center">
            View Referrals
            </a>
        </div>
    </div>


         <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm md:col-span-2 xl:col-span-1">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">ACCOUNT SECURITY</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Manage your password and account access settings.
                        </p>
                        <div class="space-y-2">
                            <a href="{{ url('/change-password') }}" class="block w-full px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition text-center">Change password</a>
                            <a href="{{ url('/delete-account') }}" class="block w-full px-4 py-2 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition text-center">Delete account</a>
                        </div>
                    </div>
            </div>

            <div class="mt-8 bg-white rounded-xl border border-gray-100 p-5 sm:p-6">
                <p class="text-gray-700 font-medium mb-2">
                    You can be found on Hotescorts with the following URLs
                </p>
                <p class="text-pink-600 font-semibold break-all">
                    Hotescorts.com.au/escorts/vic/melbourne/sourabh-wadhwa
                </p>
            </div>
        @endif
    </div>
</div>

<script>
function copyReferralCode() {
    const code = document.getElementById('referralCode').innerText;
    const btn = document.getElementById('copyBtn');

    navigator.clipboard.writeText(code).then(() => {
        btn.innerText = 'Copied';
        setTimeout(() => {
            btn.innerText = 'Copy';
        }, 2000);
    }).catch(() => {
        alert('Copy failed');
    });
}
</script>
@endsection
