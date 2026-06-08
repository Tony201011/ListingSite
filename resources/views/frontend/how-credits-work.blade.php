@extends('layouts.frontend')

@section('title', $page?->title ?: 'How Credits Work')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $page?->title ?: 'How Credits Work' }}</h1>
            <p class="mt-3 text-gray-600">{{ $page?->subtitle ?: 'Learn how credits are used to keep your listing live and visible on the platform.' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            @if(!empty($page?->content))
                <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700">
                    {!! $page->content !!}
                </article>
            @else
                <div class="space-y-6 text-gray-700">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">One credit per day your listing is online</h2>
                        <p class="text-gray-600 leading-7">Credits are consumed only when your profile is live and visible on the platform. If your profile is offline, paused, or hidden — no credits are deducted. You stay in full control.</p>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">What's included with each credit</h2>
                        <ul class="list-disc pl-6 space-y-1 text-gray-600">
                            <li>2 × daily Available NOW windows (2 hours each)</li>
                            <li>2 × daily Online NOW status (30 minutes each)</li>
                            <li>Unlimited photos &amp; videos</li>
                            <li>Unlimited touring profiles</li>
                            <li>Daily social media promotions</li>
                            <li>Your personalised short profile URL</li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Credit expiry</h2>
                        <p class="text-gray-600 leading-7">Credits have an expiry date. Unused credits may expire after the period stated at the time of purchase. Check our <a href="{{ route('credit-usage-and-expiry-policy') }}" class="text-pink-600 hover:text-pink-700">Credit Usage and Expiry Policy</a> for full details.</p>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Buying credits</h2>
                        <p class="text-gray-600 leading-7">Purchase credits in packages from your dashboard. The more credits you buy, the better the value. View all available packages on the <a href="{{ route('pricing') }}" class="text-pink-600 hover:text-pink-700">Pricing page</a>.</p>
                    </div>

                    <div class="flex gap-3 flex-wrap pt-2">
                        <a href="{{ route('pricing') }}" class="inline-flex rounded-md bg-pink-500 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-600">View Pricing</a>
                        <a href="{{ route('contact-us') }}" class="inline-flex rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Contact Support</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
