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
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Purchase History</h1>
                <p class="mt-3 text-gray-600">View all your credit purchase transactions.</p>
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

        @if(session('complaint_success'))
            <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm">
                {{ session('complaint_success') }}
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

        <!-- Daily Purchases Line Graph -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Purchases Day by Day</h2>
            <div class="relative" style="height:220px">
                <canvas id="purchasesLineChart"></canvas>
            </div>
        </div>

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
                <div class="overflow-x-auto rounded-lg">
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
                                        @php
                                            $latestComplaint = $purchase->complaints->first();
                                        @endphp
                                        <div
                                            x-data="{
                                                open: false,
                                                dropTop: 0,
                                                dropRight: 0,
                                                openDropdown() {
                                                    const r = this.$refs.btn.getBoundingClientRect();
                                                    this.dropTop = r.bottom + 4; // 4px gap between button and menu
                                                    this.dropRight = window.innerWidth - r.right;
                                                    this.open = true;
                                                }
                                            }"
                                            class="relative inline-block text-left"
                                        >
                                            <button
                                                x-ref="btn"
                                                type="button"
                                                @click="open ? (open = false) : openDropdown()"
                                                @click.outside="open = false"
                                                @scroll.window="open = false"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                                            >
                                                Action
                                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <template x-teleport="body">
                                            <div
                                                x-show="open"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                :style="`top: ${dropTop}px; right: ${dropRight}px`"
                                                class="fixed z-50 w-44 origin-top-right rounded-xl border border-gray-100 bg-white py-1 shadow-lg"
                                            >
                                                {{-- View --}}
                                                <button
                                                    type="button"
                                                    @click="open = false"
                                                    onclick="openViewModal(
                                                        '{{ addslashes($purchase->invoice_name ?? 'N/A') }}',
                                                        '{{ $purchase->formatted_date }}',
                                                        {{ $purchase->credits }},
                                                        '{{ $purchase->formatted_amount }}',
                                                        '{{ ucfirst($purchase->status) }}'
                                                    )"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                >
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View
                                                </button>

                                                {{-- Wallet Summary --}}
                                                <button
                                                    type="button"
                                                    @click="open = false"
                                                    onclick="openWalletModal()"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                >
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                    </svg>
                                                    Wallet Summary
                                                </button>

                                                {{-- View Receipt --}}
                                                @if($purchase->normalized_receipt_url)
                                                    <a
                                                        href="{{ $purchase->normalized_receipt_url }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        @click="open = false"
                                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        View Receipt
                                                    </a>
                                                @else
                                                    <span class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-400 cursor-not-allowed">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        View Receipt
                                                    </span>
                                                @endif

                                                {{-- Refund --}}
                                                @if($purchase->status === 'paid' && !$latestComplaint)
                                                    <button
                                                        type="button"
                                                        @click="open = false"
                                                        onclick="openComplaintModal({{ $purchase->id }})"
                                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                        </svg>
                                                        Refund
                                                    </button>
                                                @elseif($latestComplaint)
                                                    <span class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-400 cursor-not-allowed">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                        </svg>
                                                        Refund
                                                    </span>
                                                @else
                                                    <span class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-400 cursor-not-allowed">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                        </svg>
                                                        Refund
                                                    </span>
                                                @endif
                                            </div>
                                            </template>
                                        </div>
                                        @if($latestComplaint)
                                            <div class="mt-1">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    @if($latestComplaint->status === 'reviewed') bg-green-100 text-green-800
                                                    @elseif($latestComplaint->status === 'closed') bg-gray-100 text-gray-700
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    Complaint: {{ ucfirst($latestComplaint->status) }}
                                                </span>
                                                @if($latestComplaint->admin_reply && $latestComplaint->replied_at)
                                                    <p class="mt-1 text-xs text-gray-500">Admin replied on {{ $latestComplaint->replied_at->format('d M Y') }}</p>
                                                @endif
                                            </div>
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

<!-- View Transaction Modal -->
<div id="view-transaction-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Transaction Details</h2>
            <button type="button" onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-sm font-medium text-gray-500">Invoice Name</span>
                <span id="view-invoice-name" class="text-sm font-semibold text-gray-900"></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-sm font-medium text-gray-500">Date</span>
                <span id="view-date" class="text-sm text-gray-700"></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-sm font-medium text-gray-500">Credits</span>
                <span id="view-credits" class="text-sm text-gray-700"></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-sm font-medium text-gray-500">Amount</span>
                <span id="view-amount" class="text-sm font-semibold text-gray-900"></span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Status</span>
                <span id="view-status" class="text-sm font-semibold"></span>
            </div>
        </div>
        <div class="flex justify-end border-t border-gray-100 px-6 py-4">
            <button type="button" onclick="closeViewModal()" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Wallet Summary Modal -->
<div id="wallet-summary-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Wallet Summary</h2>
            <button type="button" onclick="closeWalletModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center">
                    <p class="text-xs font-medium text-emerald-600 mb-1">Current Balance</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ $walletSummary['current_balance'] }}</p>
                    <p class="text-xs text-emerald-500 mt-1">credits</p>
                </div>
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-center">
                    <p class="text-xs font-medium text-blue-600 mb-1">Total Purchased</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $walletSummary['total_purchased'] }}</p>
                    <p class="text-xs text-blue-500 mt-1">credits</p>
                </div>
                <div class="rounded-xl border border-rose-100 bg-rose-50 p-4 text-center">
                    <p class="text-xs font-medium text-rose-600 mb-1">Total Spent</p>
                    <p class="text-2xl font-bold text-rose-700">{{ $walletSummary['total_spent'] }}</p>
                    <p class="text-xs text-rose-500 mt-1">credits</p>
                </div>
            </div>
        </div>
        <div class="flex justify-end border-t border-gray-100 px-6 py-4">
            <button type="button" onclick="closeWalletModal()" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Complaint Modal -->
<div id="complaint-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Submit a Complaint</h2>
            <button type="button" onclick="closeComplaintModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="complaint-form" method="POST" action="" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label for="complaint-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="subject"
                    id="complaint-subject"
                    required
                    maxlength="255"
                    placeholder="Brief description of your issue..."
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                >
            </div>
            <div>
                <label for="complaint-message" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-rose-500">*</span></label>
                <textarea
                    name="message"
                    id="complaint-message"
                    required
                    maxlength="5000"
                    rows="5"
                    placeholder="Please describe your complaint in detail..."
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100 resize-none"
                ></textarea>
                <p class="mt-1 text-xs text-gray-400">Maximum 5000 characters.</p>
            </div>
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" onclick="closeComplaintModal()" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                    Submit Complaint
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const statusColorMap = {
    'Paid': 'text-green-700',
    'Pending': 'text-yellow-700',
    'Failed': 'text-red-700',
};

function openViewModal(invoiceName, date, credits, amount, status) {
    document.getElementById('view-invoice-name').textContent = invoiceName;
    document.getElementById('view-date').textContent = date;
    document.getElementById('view-credits').textContent = credits;
    document.getElementById('view-amount').textContent = amount;
    var statusEl = document.getElementById('view-status');
    statusEl.textContent = status;
    statusEl.className = 'text-sm font-semibold ' + (statusColorMap[status] || 'text-gray-700');
    var modal = document.getElementById('view-transaction-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeViewModal() {
    var modal = document.getElementById('view-transaction-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openWalletModal() {
    var modal = document.getElementById('wallet-summary-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeWalletModal() {
    var modal = document.getElementById('wallet-summary-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openComplaintModal(transactionId) {
    var complaintBasePath = '{{ url('/purchase-history') }}';
    document.getElementById('complaint-form').action = complaintBasePath + '/' + encodeURIComponent(transactionId) + '/complaint';
    var modal = document.getElementById('complaint-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeComplaintModal() {
    var modal = document.getElementById('complaint-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('complaint-form').reset();
}

document.getElementById('complaint-modal').addEventListener('click', function(e) {
    if (e.target === this) closeComplaintModal();
});

document.getElementById('view-transaction-modal').addEventListener('click', function(e) {
    if (e.target === this) closeViewModal();
});

document.getElementById('wallet-summary-modal').addEventListener('click', function(e) {
    if (e.target === this) closeWalletModal();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js" integrity="sha384-jb8JQMbMoBUzgWatfe6COACi2ljcDdZQ2OxczGA3bGNeWe+6DChMTBJemed7ZnvJ" crossorigin="anonymous"></script>
<script>
(function () {
    var labels  = @json($chartData['labels']);
    var amounts = @json($chartData['amounts']);
    var counts  = @json($chartData['counts']);

    var ctx = document.getElementById('purchasesLineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Amount (AUD)',
                    data: amounts,
                    borderColor: '#e04ecb',
                    backgroundColor: 'rgba(224,78,203,0.1)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yAmount',
                },
                {
                    label: 'Purchases',
                    data: counts,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'yCount',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.dataset.yAxisID === 'yAmount') {
                                return ' AUD $' + ctx.parsed.y.toFixed(2);
                            }
                            return ' ' + ctx.parsed.y + ' purchase' + (ctx.parsed.y !== 1 ? 's' : '');
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { maxTicksLimit: 15, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                yAmount: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    ticks: { font: { size: 11 }, callback: function (v) { return '$' + v; } },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                yCount: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    ticks: { font: { size: 11 }, stepSize: 1 },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
}());
</script>

@endsection
