@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Credit History</h1>
                <p class="mt-3 text-gray-600">Track every credit movement for your account.</p>
            </div>
            <a href="{{ route('my-profile') }}" class="text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">&larr; Back to dashboard</a>
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
                <div class="mt-1 text-xs text-gray-500">{{ $month !== 'all' ? date('M Y', strtotime($month.'-01')) : 'This month' }}</div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Credits Used</div>
                <div class="mt-1 text-3xl font-bold text-[#e04ecb]">-{{ $creditsUsed }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $month !== 'all' ? date('M Y', strtotime($month.'-01')) : 'This month' }}</div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Opening Balance</div>
                <div class="mt-1 text-3xl font-bold text-gray-900">{{ $openingBalance }}</div>
                <div class="mt-1 text-xs text-gray-500">Forwarded from last period</div>
            </div>
        </div>

        <form method="GET" action="{{ url('/credit-history') }}" class="mb-5 grid grid-cols-1 gap-2 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm md:grid-cols-2 xl:grid-cols-4" aria-label="Credit history controls">
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none ring-0 transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100 md:col-span-2 xl:col-span-2"
                placeholder="Search description..."
            />

            <select name="type" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100" aria-label="Filter transaction type">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All types</option>
                <option value="used" {{ $type === 'used' ? 'selected' : '' }}>Credits used</option>
                <option value="received" {{ $type === 'received' ? 'selected' : '' }}>Credits received</option>
            </select>

            <select name="month" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100" aria-label="Filter month">
                <option value="all" {{ $month === 'all' ? 'selected' : '' }}>All months</option>
                @foreach($availableMonths as $availableMonth)
                    <option value="{{ $availableMonth['value'] }}" {{ $month === $availableMonth['value'] ? 'selected' : '' }}>
                        {{ $availableMonth['label'] }}
                    </option>
                @endforeach
            </select>

            <div class="xl:col-span-4 flex flex-wrap items-center justify-between gap-2 pt-1">
                <p class="text-xs text-gray-500">Showing {{ $filteredLogs->count() }} of {{ $filteredLogs->total() }} entries</p>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/credit-history') }}" class="inline-flex h-10 items-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Reset</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-[#e04ecb] px-4 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">Apply filter</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
            @if($filteredLogs->count() > 0)
                <table class="min-w-[760px] w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Date</th>
                            <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Description</th>
                            <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Credits Used</th>
                            <th class="whitespace-nowrap border-b border-gray-200 px-4 py-3 text-left text-sm font-semibold text-gray-500">Credits Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filteredLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $log->formatted_date }}</td>
                                <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $log->description }}</td>
                                <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $log->amount < 0 ? abs($log->amount) : '-' }}</td>
                                <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-700">{{ $log->amount > 0 ? $log->amount : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-gray-100 px-4 py-4">
                    {{ $filteredLogs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="px-4 py-10 text-center text-sm text-gray-600">
                    No credit activity found.
                    @if($q || $type !== 'all' || $month !== 'all')
                        <a href="{{ url('/credit-history') }}" class="font-semibold text-[#e04ecb] hover:text-[#c13ab0] hover:underline">Clear filters</a>
                    @else
                        <a href="{{ route('purchase-credit') }}" class="font-semibold text-[#e04ecb] hover:text-[#c13ab0] hover:underline">Buy credits</a> to get started.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
