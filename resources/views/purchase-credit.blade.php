@extends('layouts.frontend')

@section('content')
@php
    $currentBalance = 21;
    $selectedCredits = (int) old('credits', 30);

    $plans = [
        ['credits' => 7, 'price' => 10],
        ['credits' => 30, 'price' => 35],
        ['credits' => 60, 'price' => 65],
        ['credits' => 120, 'price' => 120],
        ['credits' => 180, 'price' => 160],
    ];
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8" x-data="{ selectedPlan: {{ $selectedCredits }} }">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="m-0 text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Purchase Credits</h1>
                <p class="mt-2 text-sm text-gray-600">Choose a credit package and continue to checkout.</p>
            </div>
            <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">&larr; Back to dashboard</a>
        </div>

        <div class="mb-5 rounded-2xl border border-pink-100 bg-pink-50 p-4 text-sm text-gray-700 shadow-sm">
            Your current credits balance is <span class="font-semibold text-gray-900">{{ $currentBalance }}</span>.
            You are charged <span class="font-semibold text-gray-900">1 credit per day</span> while your profile is visible.
        </div>

        @if(session('checkout_success'))
            <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm">
                {{ session('checkout_success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm text-rose-700 shadow-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
            <form action="{{ route('purchase-credit.checkout') }}" method="POST" class="space-y-5">
                @csrf

                <div class="rounded-xl border border-gray-100">
                    @foreach($plans as $index => $plan)
                        <label class="flex cursor-pointer items-center justify-between gap-3 px-4 py-4 {{ $index < count($plans) - 1 ? 'border-b border-gray-100' : '' }}">
                            <div class="flex items-center gap-3">
                                <input
                                    type="radio"
                                    name="credits"
                                    value="{{ $plan['credits'] }}"
                                    class="h-4 w-4 border-gray-300 text-[#e04ecb] focus:ring-pink-200"
                                    @change="selectedPlan = {{ $plan['credits'] }}"
                                    {{ $plan['credits'] === $selectedCredits ? 'checked' : '' }}
                                >
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $plan['credits'] }} credits</p>
                                    <p class="text-xs text-gray-500">AUD ${{ number_format($plan['price'], 2) }} (incl. GST)</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="selectedPlan = {{ $plan['credits'] }}; $el.closest('label').querySelector('input').checked = true"
                                class="rounded-lg border border-[#e04ecb] px-3 py-1.5 text-xs font-semibold text-[#e04ecb] transition hover:bg-pink-50"
                            >
                                Select
                            </button>
                        </label>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label for="invoice_name" class="mb-2 block text-sm font-semibold text-gray-700">
                            Invoice Name
                            <span class="font-normal text-gray-500">(displayed on invoice)</span>
                        </label>
                        <input
                            id="invoice_name"
                            name="invoice_name"
                            type="text"
                            value="{{ old('invoice_name', 'Sourabh wadhwa') }}"
                            class="h-11 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                        >
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Selected package</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900"><span x-text="selectedPlan"></span> credits</p>
                        <p class="mt-1 text-xs text-gray-500">Final payment is shown at checkout.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <p class="text-xs text-gray-500">All prices are in Australian Dollars (AUD) and include GST.</p>
                    <button type="submit" class="inline-flex h-11 items-center rounded-full bg-[#e04ecb] px-6 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">
                        Continue to checkout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
