@extends('layouts.frontend')

@section('content')
@php
    $purchases = [
        [
            'date' => '03 Mar 2026',
            'credits' => 30,
            'price' => '$49.00',
            'status' => 'Paid',
            'invoice_url' => '#',
        ],
        [
            'date' => '25 Feb 2026',
            'credits' => 60,
            'price' => '$89.00',
            'status' => 'Pending',
            'invoice_url' => null,
        ],
        [
            'date' => '14 Feb 2026',
            'credits' => 15,
            'price' => '$25.00',
            'status' => 'Failed',
            'invoice_url' => null,
        ],
        [
            'date' => '01 Feb 2026',
            'credits' => 120,
            'price' => '$159.00',
            'status' => 'Paid',
            'invoice_url' => '#',
        ],
    ];
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
                    @forelse($purchases as $purchase)
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
                                You haven't purchased credits yet.
                                <a href="{{ url('/purchase-credit') }}" class="font-semibold text-[#e04ecb] hover:text-[#c13ab0] hover:underline">Click here to buy credits</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
