@extends('layouts.frontend')

@section('content')
@php
    $statusColors = [
        'pending'  => 'bg-amber-100 text-amber-800 border-amber-200',
        'approved' => 'bg-green-100 text-green-800 border-green-200',
        'rejected' => 'bg-red-100 text-red-800 border-red-200',
    ];
@endphp

<div class="min-h-screen bg-gray-50">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="min-h-[400px] rounded-lg bg-white p-6 shadow-sm sm:p-8">

            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex items-center text-pink-500 hover:text-pink-600 transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <h1 class="text-3xl font-bold mb-2 text-gray-900">Account Restoration Requests</h1>
            <p class="text-gray-600 mb-8 text-sm">View your submitted account restoration requests and admin responses.</p>

            @if(session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($requests->isEmpty())
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-8 text-center">
                    <svg class="mx-auto mb-3 h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">No restoration requests found.</p>
                </div>
            @else
                <div class="space-y-5">
                    @foreach($requests as $request)
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                                <div>
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Request #{{ $request->id }}</span>
                                    <p class="text-xs text-gray-400 mt-0.5">Submitted: {{ $request->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                <span class="inline-flex items-center rounded-full border px-3 py-0.5 text-xs font-semibold capitalize {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                    {{ $request->status }}
                                </span>
                            </div>

                            @if($request->request_reason)
                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Your Message</p>
                                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $request->request_reason }}</p>
                                </div>
                            @endif

                            @if($request->admin_reply)
                                <div class="rounded-lg border {{ $request->status === 'approved' ? 'border-green-200 bg-green-50' : ($request->status === 'rejected' ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50') }} p-4">
                                    <p class="text-xs font-semibold {{ $request->status === 'approved' ? 'text-green-700' : ($request->status === 'rejected' ? 'text-red-700' : 'text-amber-700') }} uppercase tracking-wide mb-1">Admin Reply</p>
                                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $request->admin_reply }}</p>
                                    @if($request->reviewed_at)
                                        <p class="text-xs text-gray-500 mt-2">Reviewed: {{ $request->reviewed_at->format('M d, Y h:i A') }}</p>
                                    @endif
                                </div>
                            @elseif($request->status === 'pending')
                                <div class="rounded-lg border border-amber-100 bg-amber-50 p-3">
                                    <p class="text-xs text-amber-700">Your request is pending review. You will be notified once it has been processed.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </main>
</div>
@endsection
