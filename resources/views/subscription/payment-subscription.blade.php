@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Choose Your Plan</h1>
            <p class="mt-3 text-base text-gray-600">Purchase a credit package to keep your profile active and visible.</p>
        </div>

        @if($packages->isEmpty())
            <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
                <p class="text-sm text-gray-500">No subscription plans are currently available. Please check back later.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $package)
                    <div class="flex flex-col rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex-1">
                            <p class="text-lg font-bold text-gray-900">{{ $package->credits }} Credits</p>
                            <p class="mt-1 text-3xl font-extrabold text-gray-900">
                                AUD ${{ number_format($package->total_price, 2) }}
                            </p>
                            @if($package->price_per_credit)
                                <p class="mt-1 text-sm text-gray-500">
                                    ${{ number_format($package->price_per_credit, 4) }} per credit
                                </p>
                            @endif
                        </div>
                        <a
                            href="{{ route('purchase-credit') }}"
                            class="mt-6 block rounded-full bg-[#e04ecb] px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-[#c13ab0]"
                        >
                            Buy Now
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-8 text-center">
            <a href="{{ route('purchase-credit') }}" class="text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">
                View all credit packages &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
