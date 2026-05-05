@extends('layouts.frontend')

@section('content')
@php
    $q = trim((string) request('q', ''));
    $status = (string) request('status', 'all');
    $month = (string) request('month', 'all');
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-6xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="m-0 text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Purchase History</h1>
                <p class="mt-2 text-sm text-gray-600">View all your credit purchase transactions.</p>
            </div>
            <a href="{{ route('purchase-credit') }}" class="inline-flex items-center rounded-lg bg-[#e04ecb] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Buy Credits
            </a>
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

        <!-- Filters -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-0">
                    <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        type="text"
                        name="q"
                        id="q"
                        value="{{ $q }}"
                        placeholder="Search by invoice name, credits, or amount..."
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                    >
                </div>

                <div class="w-full sm:w-auto">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                        name="status"
                        id="status"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                    >
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <select
                        name="month"
                        id="month"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                    >
                        <option value="all" {{ $month === 'all' ? 'selected' : '' }}>All Months</option>
                        @foreach($availableMonths as $availableMonth)
                            <option value="{{ $availableMonth['value'] }}" {{ $month === $availableMonth['value'] ? 'selected' : '' }}>
                                {{ $availableMonth['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-[#e04ecb] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">
                        Filter
                    </button>
                    @if($q || $status !== 'all' || $month !== 'all')
                        <a href="{{ route('purchase-history') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            @if($purchases->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="whitespace-nowrap px-6 py-4 text-left text-sm font-semibold text-gray-500">Date</th>
                                <th class="whitespace-nowrap px-6 py-4 text-left text-sm font-semibold text-gray-500">Invoice Name</th>
                                <th class="whitespace-nowrap px-6 py-4 text-left text-sm font-semibold text-gray-500">Credits</th>
                                <th class="whitespace-nowrap px-6 py-4 text-left text-sm font-semibold text-gray-500">Amount</th>
                                <th class="whitespace-nowrap px-6 py-4 text-left text-sm font-semibold text-gray-500">Status</th>
                                <th class="whitespace-nowrap px-6 py-4 text-left text-sm font-semibold text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($purchases as $purchase)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $purchase->formatted_date }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $purchase->invoice_name ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $purchase->credits }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $purchase->formatted_amount }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            @if($purchase->status === 'paid') bg-green-100 text-green-800
                                            @elseif($purchase->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($purchase->status === 'failed') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($purchase->status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        @if($purchase->status === 'paid' && $purchase->stripe_session_id)
                                            <a href="#" onclick="viewReceipt('{{ $purchase->stripe_session_id }}')" class="text-[#e04ecb] hover:text-[#c13ab0] font-medium">
                                                View Receipt
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $purchases->appends(request()->query())->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($q || $status !== 'all' || $month !== 'all')
                            Try adjusting your filters or <a href="{{ route('purchase-history') }}" class="text-[#e04ecb] hover:text-[#c13ab0]">clear all filters</a>.
                        @else
                            You haven't made any purchases yet. <a href="{{ route('purchase-credit') }}" class="text-[#e04ecb] hover:text-[#c13ab0]">Buy credits</a> to get started.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function viewReceipt(sessionId) {
    // Open Stripe receipt in new window
    window.open('https://dashboard.stripe.com/test/payments/' + sessionId, '_blank');
}
</script>
@endsection
