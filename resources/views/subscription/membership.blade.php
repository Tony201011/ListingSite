@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-7xl">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight sm:text-4xl">Choose Your Membership</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-gray-600 sm:text-base">
                Activate your profile and increase visibility with a plan that matches your promotion goals.
            </p>
        </div>

        @if($packages->isEmpty())
            <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
                <p class="text-sm text-gray-500">No membership plans are currently available. Please check back later.</p>
            </div>
        @else
            <div class="mb-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $package)
                    <div class="flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="bg-[#e04ecb] px-5 py-4 text-white">
                            <h2 class="text-xl font-bold">{{ $package->name }}</h2>
                            @if($package->description)
                                <p class="mt-1 text-sm text-white/90">{{ $package->description }}</p>
                            @endif
                        </div>

                        <div class="border-b border-gray-100 bg-gray-50 p-4">
                            <div class="rounded-lg border border-gray-200 bg-white p-3 text-center">
                                <p class="text-2xl font-bold text-gray-900">AUD ${{ number_format($package->price, 2) }}</p>
                                <p class="text-xs font-medium text-gray-500">{{ $package->total_credits }} credits</p>
                            </div>
                        </div>

                        <div class="p-5">
                            <a href="{{ route('purchase-credit', ['package_id' => $package->id, 'lock_package' => 1]) }}" class="block w-full rounded-lg bg-[#e04ecb] px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-[#c13ab0]">Choose Plan</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
