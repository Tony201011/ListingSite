@extends('layouts.frontend')

@section('content')
@php
    $openingBalance = 21;

    $transactions = [
        ['date' => '03 Mar 2026', 'description' => 'Profile Boost (24h)', 'type' => 'used', 'amount' => 4, 'status' => 'Completed'],
        ['date' => '02 Mar 2026', 'description' => 'Credit Top-up (Card)', 'type' => 'received', 'amount' => 12, 'status' => 'Completed'],
        ['date' => '02 Mar 2026', 'description' => 'Message Unlock', 'type' => 'used', 'amount' => 2, 'status' => 'Completed'],
        ['date' => '01 Mar 2026', 'description' => 'Referral Reward', 'type' => 'received', 'amount' => 6, 'status' => 'Completed'],
        ['date' => '01 Mar 2026', 'description' => 'Daily Visibility Fee', 'type' => 'used', 'amount' => 1, 'status' => 'Completed'],
    ];

    $creditsReceived = collect($transactions)->where('type', 'received')->sum('amount');
    $creditsUsed = collect($transactions)->where('type', 'used')->sum('amount');
    $currentBalance = $openingBalance + $creditsReceived - $creditsUsed;
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="m-0 text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Credit History</h1>
                <p class="mt-2 text-sm text-gray-600">Track every credit movement for your account.</p>
            </div>
            <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">&larr; Back to dashboard</a>
        </div>

        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-pink-100 bg-pink-50 px-4 py-2 text-sm font-semibold text-gray-800">
            <span class="inline-block h-2 w-2 rounded-full bg-[#e04ecb]" aria-hidden="true"></span>
            <span>Profile is visible</span>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-pink-100 bg-pink-50 p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Current Balance</div>
                <div class="mt-1 text-3xl font-bold text-gray-900">{{ $currentBalance }}</div>
                <div class="mt-1 text-xs text-gray-500">Available credits</div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Credits Received</div>
                <div class="mt-1 text-3xl font-bold text-[#e04ecb]">+{{ $creditsReceived }}</div>
                <div class="mt-1 text-xs text-gray-500">This month</div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Credits Used</div>
                <div class="mt-1 text-3xl font-bold text-[#e04ecb]">-{{ $creditsUsed }}</div>
                <div class="mt-1 text-xs text-gray-500">This month</div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Opening Balance</div>
                <div class="mt-1 text-3xl font-bold text-gray-900">{{ $openingBalance }}</div>
                <div class="mt-1 text-xs text-gray-500">Forwarded from last month</div>
            </div>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3" aria-label="Credit history controls">
            <input
                type="text"
                class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none ring-0 transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100 md:col-span-2 xl:col-span-1"
                placeholder="Search by description"
            />

            <select class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100" aria-label="Filter transaction type">
                <option>All types</option>
                <option>Credits used</option>
                <option>Credits received</option>
            </select>

            <select class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100" aria-label="Filter month">
                <option>March 2026</option>
                <option>February 2026</option>
            </select>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-[760px] w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Date</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Description</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Credits Used</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Credits Received</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $item)
                        <tr>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['date'] }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['description'] }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['type'] === 'used' ? $item['amount'] : '-' }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['type'] === 'received' ? $item['amount'] : '-' }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">
                                <span class="inline-flex items-center rounded-full border border-pink-200 bg-pink-50 px-2.5 py-1 text-xs font-semibold text-[#e04ecb]">{{ $item['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700">Monthly total</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ $creditsUsed }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ $creditsReceived }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700">Balance: {{ $currentBalance }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
