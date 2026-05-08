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
                                        @if($purchase->status === 'paid')
                                            @if($latestComplaint)
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    @if($latestComplaint->status === 'reviewed') bg-green-100 text-green-800
                                                    @elseif($latestComplaint->status === 'closed') bg-gray-100 text-gray-700
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    Complaint: {{ ucfirst($latestComplaint->status) }}
                                                </span>
                                                @if($latestComplaint->admin_reply && $latestComplaint->replied_at)
                                                    <p class="mt-1 text-xs text-gray-500">Admin replied on {{ $latestComplaint->replied_at->format('d M Y') }}</p>
                                                @endif
                                            @else
                                                <div class="flex items-center gap-3">
                                                    @if($purchase->receipt_url)
                                                        <a href="{{ $purchase->receipt_url }}" target="_blank" rel="noopener noreferrer" class="text-[#e04ecb] hover:text-[#c13ab0] font-medium">
                                                            View Receipt
                                                        </a>
                                                    @endif
                                                    <button
                                                        type="button"
                                                        onclick="openComplaintModal({{ $purchase->id }})"
                                                        class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                                    >
                                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                        </svg>
                                                        Complaint
                                                    </button>
                                                </div>
                                            @endif
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
</script>

@endsection
