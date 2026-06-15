@extends('layouts.frontend')

@section('title', 'Pricing')

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

            <h1 class="text-3xl font-bold text-gray-900">{{ $page?->title ?: 'Pricing' }}</h1>
            <p class="mt-2 mb-8 text-gray-600">{{ $page?->subtitle ?: 'One credit for every day your profile is online, simple and fair for all.' }}</p>

            <div class="border border-gray-300 rounded-lg p-6">
                @if(!empty($page?->intro_content))
                    <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700">
                        {!! $page->intro_content !!}
                    </article>
                @else
                    <p class="text-gray-600 leading-7 mb-4">
                        We don't believe in basic, pro and premium packages. Every babe gets the same features. Just one credit for every day you advertise.
                    </p>
                    <p class="text-gray-600 leading-7 mb-4">
                        Not advertising, taking a break, or hiding your profile? No charge, no worries! You can still upload new pictures and update your profile content without paying extra. On the days your profile is offline, you don't pay &mdash; you only pay when your profile is online.
                    </p>
                    <p class="font-semibold text-gray-700 mb-2">One credit for every day your profile is online, simple and fair for all.</p>

                    <p class="text-gray-600 mb-1">This includes:</p>
                    <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-1">
                        <li>2 x daily Available NOW (2 x 2 hours)</li>
                        <li>2 x daily Online NOW (2 x 30 mins)</li>
                        <li>Unlimited photos &amp; videos</li>
                        <li>Unlimited touring profiles</li>
                        <li>Daily Twitter promotions</li>
                        <li>Your short profile URL</li>
                    </ul>
                @endif

                <div class="rounded-xl border border-pink-100 bg-pink-50/40 p-4 sm:p-5 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Credit system at a glance</h2>
                    <p class="text-gray-700 mb-3">Advertisers purchase prepaid advertising credits. Credits are used for profile visibility and promotional listing features only.</p>
                    <p class="text-gray-700 mb-3">All payments on this platform are exclusively for purchasing advertising credits and promotional listing packages. The platform does not process bookings, deposits, appointment payments, escort payments, or payments between visitors and advertisers.</p>
                    <ul class="list-disc pl-6 space-y-1 text-gray-700">
                        <li>1 credit keeps one approved profile visible for one day.</li>
                        <li>Credits are not deducted while a profile is hidden, suspended, or under review.</li>
                        <li>If the credit balance reaches zero, the profile is paused automatically.</li>
                        <li>Used credits are not refundable.</li>
                        <li>Unused credits may be handled according to the <a href="{{ route('refund-policy') }}" class="text-pink-600 hover:text-pink-700">Refund Policy</a>.</li>
                    </ul>
                </div>

                @auth
                    <a href="{{ url('/purchase-credit') }}" class="inline-flex rounded-md bg-pink-500 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-600">Buy credits</a>
                @else
                    <a href="{{ route('signin') }}" class="inline-flex rounded-md bg-pink-500 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-600">Login to buy credits</a>
                @endauth

                <h3 class="text-xl font-bold text-gray-900 tracking-tight mt-10 mb-3">{{ $page?->packages_title ?: 'Packages' }}</h3>

                @if(!empty($packages) && $packages->count() > 0)
                    <p class="text-gray-600 mb-4">You can purchase your credits in the following packages:</p>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full min-w-[360px] text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-3 py-3 text-left font-semibold sm:px-4">Credits</th>
                                    <th class="px-3 py-3 text-left font-semibold sm:px-4">Total Price</th>
                                    <th class="px-3 py-3 text-right font-semibold sm:px-4">Price per credit</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @foreach($packages as $package)
                                    <tr class="border-t border-gray-100 {{ $loop->odd ? 'bg-white' : 'bg-gray-50' }}">
                                        <td class="px-3 py-3 sm:px-4">{{ $package->total_credits }}</td>
                                        <td class="px-3 py-3 font-semibold sm:px-4">{{ $package->total_price }}</td>
                                        <td class="px-3 py-3 text-right sm:px-4">{{ $package->price_per_credit }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif(!empty($page?->packages_content))
                    <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700 [&_table]:w-full [&_table]:text-sm [&_th]:px-4 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-left [&_td]:px-4 [&_td]:py-3 [&_table]:border [&_table]:border-gray-200 [&_thead]:bg-gray-100">
                        {!! $page->packages_content !!}
                    </article>
                @else
                    <p class="text-gray-600 mb-4">You can purchase your credits in the following packages:</p>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full min-w-[360px] text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-3 py-3 text-left font-semibold sm:px-4">Credits</th>
                                    <th class="px-3 py-3 text-left font-semibold sm:px-4">Total Price</th>
                                    <th class="px-3 py-3 text-right font-semibold sm:px-4">Price per credit</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr class="border-t border-gray-100 bg-white">
                                    <td class="px-3 py-3 sm:px-4">7</td>
                                    <td class="px-3 py-3 font-semibold sm:px-4">10 AUD $</td>
                                    <td class="px-3 py-3 text-right sm:px-4">AUD $1.43</td>
                                </tr>
                                <tr class="border-t border-gray-100 bg-gray-50">
                                    <td class="px-3 py-3 sm:px-4">30</td>
                                    <td class="px-3 py-3 font-semibold sm:px-4">35 AUD $</td>
                                    <td class="px-3 py-3 text-right sm:px-4">AUD $1.17</td>
                                </tr>
                                <tr class="border-t border-gray-100 bg-white">
                                    <td class="px-3 py-3 sm:px-4">60</td>
                                    <td class="px-3 py-3 font-semibold sm:px-4">65 AUD $</td>
                                    <td class="px-3 py-3 text-right sm:px-4">AUD $1.08</td>
                                </tr>
                                <tr class="border-t border-gray-100 bg-gray-50">
                                    <td class="px-3 py-3 sm:px-4">120</td>
                                    <td class="px-3 py-3 font-semibold sm:px-4">120 AUD $</td>
                                    <td class="px-3 py-3 text-right sm:px-4">AUD $1.00</td>
                                </tr>
                                <tr class="border-t border-gray-100 bg-white">
                                    <td class="px-3 py-3 sm:px-4">180</td>
                                    <td class="px-3 py-3 font-semibold sm:px-4">160 AUD $</td>
                                    <td class="px-3 py-3 text-right sm:px-4">AUD $0.89</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                @auth
                    <a href="{{ url('/purchase-credit') }}" class="mt-6 inline-flex rounded-md bg-pink-500 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-600">Buy credits</a>
                @else
                    <a href="{{ route('signin') }}" class="mt-6 inline-flex rounded-md bg-pink-500 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-600">Login to buy credits</a>
                @endauth
            </div>
        </div>
    </main>
</div>
@endsection
