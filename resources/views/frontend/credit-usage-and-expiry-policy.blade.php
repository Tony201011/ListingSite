@extends('layouts.frontend')

@section('title', $policy?->title ?? 'Credit Usage and Expiry Policy')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{}">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8">
            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex items-center text-pink-500 hover:text-pink-600 transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <h1 class="text-3xl font-bold text-gray-900">{{ $policy?->title ?? 'Credit Usage and Expiry Policy' }}</h1>
            <p class="mt-2 text-gray-600 {{ $policy?->updated_at ? 'mb-2' : 'mb-8' }}">Read how credits are consumed, when they expire, and where to get support for credit usage issues.</p>
            @if($policy?->updated_at)
                <p class="mb-8 text-sm text-gray-500">Last updated: {{ $policy->updated_at->format('M d, Y') }}</p>
            @endif

            <div class="border border-gray-300 rounded-lg p-6">
                @if(!empty($policy?->content))
                    <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700 [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-lg [&_img]:my-4">
                        {!! $policy->content !!}
                    </article>
                @else
                    <div class="space-y-6 text-gray-700">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Scope of credits and platform payments</h2>
                            <p class="text-gray-600 leading-7">Advertisers purchase prepaid advertising credits for profile visibility and promotional listing features.</p>
                            <p class="text-gray-600 leading-7 mt-3">All payments on this platform are exclusively for purchasing advertising credits and promotional listing packages. The platform does not process bookings, deposits, appointment payments, escort payments, or payments between visitors and advertisers.</p>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Main credit rules</h2>
                            <ul class="list-disc pl-6 space-y-1 text-gray-600">
                                <li>1 credit keeps one approved profile visible for one day.</li>
                                <li>Credits are not deducted while a profile is hidden, suspended, or under review.</li>
                                <li>If the credit balance reaches zero, the profile is paused automatically.</li>
                                <li>Used credits are not refundable.</li>
                                <li>Unused credits may be handled according to the <a href="{{ route('refund-policy') }}" class="text-pink-600 hover:text-pink-700">refund policy</a>.</li>
                            </ul>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Support</h2>
                            <p class="text-gray-600 leading-7">For credit usage disputes or expiry questions, please <a href="{{ route('contact-us') }}" class="text-pink-600 hover:text-pink-700">contact support</a> with your account and transaction details.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection
