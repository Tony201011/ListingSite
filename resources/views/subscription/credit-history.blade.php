@extends('layouts.frontend')

@section('content')
@php
    $openingBalance = 21;

    $transactions = [
        ['date' => '03 Mar 2026', 'month' => '2026-03', 'description' => 'Profile Boost (24h)', 'type' => 'used', 'amount' => 4, 'status' => 'Completed'],
        ['date' => '02 Mar 2026', 'month' => '2026-03', 'description' => 'Credit Top-up (Card)', 'type' => 'received', 'amount' => 12, 'status' => 'Completed'],
        ['date' => '02 Mar 2026', 'month' => '2026-03', 'description' => 'Message Unlock', 'type' => 'used', 'amount' => 2, 'status' => 'Completed'],
        ['date' => '01 Mar 2026', 'month' => '2026-03', 'description' => 'Referral Reward', 'type' => 'received', 'amount' => 6, 'status' => 'Completed'],
        ['date' => '01 Mar 2026', 'month' => '2026-02', 'description' => 'Daily Visibility Fee', 'type' => 'used', 'amount' => 1, 'status' => 'Completed'],
    ];

    $q = trim((string) request('q', ''));
    $type = (string) request('type', 'all');
    $month = (string) request('month', 'all');

    $filteredTransactions = collect($transactions)
        ->filter(function ($item) use ($q, $type, $month) {
            $matchesSearch = $q === '' || str_contains(strtolower($item['date'] . ' ' . $item['description'] . ' ' . $item['status']), strtolower($q));
            $matchesType = $type === 'all' || $item['type'] === $type;
            $matchesMonth = $month === 'all' || $item['month'] === $month;

            return $matchesSearch && $matchesType && $matchesMonth;
        })
        ->values();

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

        <form method="GET" action="{{ url('/credit-history') }}" class="mb-5 grid grid-cols-1 gap-2 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm md:grid-cols-2 xl:grid-cols-4" aria-label="Credit history controls">
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none ring-0 transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100 md:col-span-2 xl:col-span-2"
                placeholder="Search date or description..."
            />

            <select name="type" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100" aria-label="Filter transaction type">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All types</option>
                <option value="used" {{ $type === 'used' ? 'selected' : '' }}>Credits used</option>
                <option value="received" {{ $type === 'received' ? 'selected' : '' }}>Credits received</option>
            </select>

            <select name="month" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100" aria-label="Filter month">
                <option value="all" {{ $month === 'all' ? 'selected' : '' }}>All months</option>
                <option value="2026-03" {{ $month === '2026-03' ? 'selected' : '' }}>March 2026</option>
                <option value="2026-02" {{ $month === '2026-02' ? 'selected' : '' }}>February 2026</option>
            </select>

            <div class="xl:col-span-4 flex flex-wrap items-center justify-between gap-2 pt-1">
                <p class="text-xs text-gray-500">Showing {{ $filteredTransactions->count() }} of {{ count($transactions) }} transactions</p>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/credit-history') }}" class="inline-flex h-10 items-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Reset</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-[#e04ecb] px-4 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">Apply filter</button>
                </div>
            </div>
        </form>

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
                    @forelse($filteredTransactions as $item)
                        <tr>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['date'] }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['description'] }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['type'] === 'used' ? $item['amount'] : '-' }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $item['type'] === 'received' ? $item['amount'] : '-' }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">
                                <span class="inline-flex items-center rounded-full border border-pink-200 bg-pink-50 px-2.5 py-1 text-xs font-semibold text-[#e04ecb]">{{ $item['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-600">
                                No transactions match your current filter.
                                <a href="{{ url('/credit-history') }}" class="font-semibold text-[#e04ecb] hover:text-[#c13ab0] hover:underline">Clear filters</a>
                            </td>
                        </tr>
                    @endforelse
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
