@extends('layouts.frontend')

@section('title', 'Processor Review Access')

@section('content')
<div class="min-h-screen bg-gray-50">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
            <button
                type="button"
                onclick="window.history.back()"
                class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-pink-500 transition-colors hover:text-pink-600"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Processor Review Access</h1>
                <p class="mt-3 text-gray-600">
                    This page summarizes the website purpose, reviewer login placeholders, and the main pages a processor reviewer may need to access.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-300 p-6">
                    <h2 class="text-xl font-semibold text-gray-900">Website</h2>
                    <p class="mt-3 text-gray-600">
                        <a href="https://hotescort.com.au" class="font-semibold text-pink-600 hover:text-pink-700" target="_blank" rel="noopener noreferrer">
                            https://hotescort.com.au
                        </a>
                    </p>
                </div>

                <div class="rounded-lg border border-gray-300 p-6">
                    <h2 class="text-xl font-semibold text-gray-900">Business model</h2>
                    <p class="mt-3 leading-7 text-gray-600">
                        HotEscort is an Australian adult advertising/listing platform. Advertisers purchase prepaid advertising credits.
                        Credits are used for profile visibility and promotional listing features. The platform does not process bookings,
                        deposits, appointment payments, escort payments, or payments between visitors and advertisers.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-300 p-6">
                    <h2 class="text-xl font-semibold text-gray-900">Read-only reviewer login</h2>
                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500">To be supplied securely for review.</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Password</dt>
                            <dd class="mt-1 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500">To be supplied securely for review.</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg border border-amber-400 bg-amber-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900">Access restriction</h2>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-base leading-7 text-gray-800">
                        <li>Real admin access is not provided for processor review.</li>
                        <li>If additional access is requested, only the limited read-only reviewer account may be shared.</li>
                        <li>No credentials with write access to users, listings, payments, credits, or settings are provided for processor reviews.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 rounded-lg border border-gray-300 p-6">
                <h2 class="text-xl font-semibold text-gray-900">Important review pages</h2>
                <p class="mt-3 text-gray-600">Some pages below require reviewer or advertiser login to access fully.</p>

                <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 divide-y divide-gray-200">
                        @php
                            $reviewPages = [
                                ['label' => 'Homepage', 'url' => url('/')],
                                ['label' => 'Pricing/credits', 'url' => route('pricing')],
                                ['label' => 'How credits work', 'url' => route('how-credits-work')],
                                ['label' => 'Advertiser dashboard', 'url' => url('/my-profile')],
                                ['label' => 'Sample listing', 'url' => route('sample-listing')],
                                ['label' => 'Checkout/test payment page', 'url' => route('purchase-credit')],
                                ['label' => 'Terms and conditions', 'url' => route('terms-and-conditions')],
                                ['label' => 'Privacy policy', 'url' => route('privacy-policy')],
                                ['label' => 'Refund policy', 'url' => route('refund-policy')],
                                ['label' => 'Credit usage and expiry policy', 'url' => route('credit-usage-and-expiry-policy')],
                                ['label' => 'Content moderation policy', 'url' => route('content-moderation-policy')],
                                ['label' => 'Age and consent policy', 'url' => route('age-and-consent-policy')],
                                ['label' => 'Prohibited content/services policy', 'url' => route('prohibited-content-policy')],
                                ['label' => 'Contact/support', 'url' => route('contact-us')],
                                ['label' => 'Report a listing', 'url' => route('report-a-listing')],
                            ];
                        @endphp

                        @foreach($reviewPages as $page)
                            <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <span class="text-sm font-medium text-gray-900">{{ $page['label'] }}</span>
                                <a
                                    href="{{ $page['url'] }}"
                                    class="break-all text-sm font-semibold text-pink-600 hover:text-pink-700"
                                    @if(str_starts_with($page['url'], 'http')) target="_blank" rel="noopener noreferrer" @endif
                                >
                                    {{ $page['url'] }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
