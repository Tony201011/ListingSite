@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        <button
            type="button"
            onclick="window.history.back()"
            class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
        >
            <span class="mr-1">&lt;</span> Go back
        </button>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My Referrals</h2>

                <p class="text-lg text-gray-600 mb-8 font-medium">
                    Invite friends with your referral link and track your referral performance in one place.
                </p>

                <div
                    class="border border-dashed border-gray-200 rounded-xl p-6 sm:p-8 bg-gray-50 mb-8"
                    x-data="referralPage({
                        referralLink: @js($referralLink)
                    })"
                >
                    <div class="max-w-xl mx-auto text-center">
                        <div class="text-5xl mb-4">🎉</div>

                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-3">
                            Invite & Earn
                        </h3>

                        <p class="text-gray-500 mb-6">
                            Share your referral link with others and grow your network while earning rewards.
                        </p>

                        <!-- Copy Link -->
                        <div class="text-left mb-6">
                            <label class="block text-sm font-medium text-gray-600 mb-2">
                                Your Referral Link
                            </label>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <input
                                    type="text"
                                    x-model="referralLink"
                                    readonly
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl bg-white text-gray-700 focus:outline-none"
                                >

                                <button
                                    type="button"
                                    @click="copyLink()"
                                    class="inline-flex justify-center items-center px-6 py-3 text-sm font-medium rounded-xl text-white bg-pink-600 hover:bg-pink-700 transition"
                                >
                                    <span x-text="buttonText"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Share -->
                        <div class="mb-6 text-left">
                            <p class="text-sm font-medium text-gray-600 mb-3">Share your link</p>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <a :href="`https://wa.me/?text=${encodeURIComponent(referralLink)}`"
                                   target="_blank"
                                   class="inline-flex justify-center items-center px-4 py-3 rounded-xl bg-green-50 text-green-700 font-medium hover:bg-green-100 transition">
                                    WhatsApp
                                </a>

                                <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(referralLink)}`"
                                   target="_blank"
                                   class="inline-flex justify-center items-center px-4 py-3 rounded-xl bg-blue-50 text-blue-700 font-medium hover:bg-blue-100 transition">
                                    Facebook
                                </a>

                                <a :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(referralLink)}`"
                                   target="_blank"
                                   class="inline-flex justify-center items-center px-4 py-3 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition">
                                    Twitter
                                </a>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="mt-8">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Your Stats</h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-white border border-gray-100 rounded-xl p-5 text-center shadow-sm">
                                    <p class="text-2xl font-bold text-gray-900">{{ $referralCount ?? 0 }}</p>
                                    <p class="text-sm text-gray-500 mt-1">Total Referrals</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/referral-page.js') }}"></script>
@endpush
