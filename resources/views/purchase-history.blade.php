@extends('layouts.frontend')

@section('content')
@php
    $purchases = [
        [
            'date' => '03 Mar 2026',
            'month' => '2026-03',
            'credits' => 30,
            'price' => '$49.00',
            'status' => 'Paid',
            'invoice_url' => '#',
        ],
        [
            'date' => '25 Feb 2026',
            'month' => '2026-02',
            'credits' => 60,
            'price' => '$89.00',
            'status' => 'Pending',
            'invoice_url' => null,
        ],
        [
            'date' => '14 Feb 2026',
            'month' => '2026-02',
            'credits' => 15,
            'price' => '$25.00',
            'status' => 'Failed',
            'invoice_url' => null,
        ],
        [
            'date' => '01 Feb 2026',
            'month' => '2026-02',
            'credits' => 120,
            'price' => '$159.00',
            'status' => 'Paid',
            'invoice_url' => '#',
        ],
    ];

    $q = trim((string) request('q', ''));
    $status = (string) request('status', 'all');
    $month = (string) request('month', 'all');

    $filteredPurchases = collect($purchases)
        ->filter(function ($purchase) use ($q, $status, $month) {
            $matchesSearch = $q === '' || str_contains(strtolower($purchase['date'] . ' ' . $purchase['price'] . ' ' . $purchase['status'] . ' ' . $purchase['credits']), strtolower($q));
            $matchesStatus = $status === 'all' || strtolower($purchase['status']) === strtolower($status);
            $matchesMonth = $month === 'all' || $purchase['month'] === $month;

            return $matchesSearch && $matchesStatus && $matchesMonth;
        })
        ->values();
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="m-0 text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Purchase History</h1>
                <p class="mt-2 text-sm text-gray-600">Review all your credit purchase transactions.</p>
            </div>
            <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">&larr; Back to dashboard</a>
        </div>

        <form method="GET" action="{{ url('/purchase-history') }}" class="mb-5 grid grid-cols-1 gap-2 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm md:grid-cols-2 xl:grid-cols-4">
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                placeholder="Search date, status, price..."
                class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none ring-0 transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100 md:col-span-2 xl:col-span-2"
            >

            <select name="status" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All status</option>
                <option value="Paid" {{ $status === 'Paid' ? 'selected' : '' }}>Paid</option>
                <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Failed" {{ $status === 'Failed' ? 'selected' : '' }}>Failed</option>
            </select>

            <select name="month" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
                <option value="all" {{ $month === 'all' ? 'selected' : '' }}>All months</option>
                <option value="2026-03" {{ $month === '2026-03' ? 'selected' : '' }}>March 2026</option>
                <option value="2026-02" {{ $month === '2026-02' ? 'selected' : '' }}>February 2026</option>
            </select>

            <div class="xl:col-span-4 flex flex-wrap items-center justify-between gap-2 pt-1">
                <p class="text-xs text-gray-500">Showing {{ $filteredPurchases->count() }} of {{ count($purchases) }} purchases</p>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/purchase-history') }}" class="inline-flex h-10 items-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Reset</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-[#e04ecb] px-4 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">Apply filter</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
            <table class="w-full min-w-[760px] border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Date</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Credits</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Price</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Status</th>
                        <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Invoice</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($filteredPurchases as $purchase)
                        @php
                            $statusClasses = match ($purchase['status']) {
                                'Paid' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                'Pending' => 'border-amber-200 bg-amber-50 text-amber-700',
                                'Failed' => 'border-rose-200 bg-rose-50 text-rose-700',
                                default => 'border-gray-200 bg-gray-50 text-gray-700',
                            };
                        @endphp
                        <tr>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $purchase['date'] }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $purchase['credits'] }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $purchase['price'] }}</td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $purchase['status'] }}</span>
                            </td>
                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">
                                @if($purchase['invoice_url'])
                                    <a href="{{ $purchase['invoice_url'] }}" class="font-medium text-[#e04ecb] hover:text-[#c13ab0] hover:underline">Download</a>
                                @else
                                    <span class="text-gray-400">Not available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-600">
                                No purchases match your current filter.
                                <a href="{{ url('/purchase-history') }}" class="font-semibold text-[#e04ecb] hover:text-[#c13ab0] hover:underline">Clear filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
