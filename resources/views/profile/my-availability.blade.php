@extends('layouts.frontend')

@section('content')
    @php
        $hasAvailability = $availabilityCount > 0;
    @endphp

    <div class="min-h-screen bg-gray-50" x-data="{}">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8">
                <button
                    type="button"
                    onclick="window.history.back()"
                    class="inline-flex items-center text-pink-500 hover:text-pink-600 transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
                >
                    <span class="mr-1">&lt;</span> back
                </button>

                <h1 class="text-3xl font-bold mb-8 text-gray-900">My Availability</h1>

                <p class="text-lg text-gray-600 mb-8 font-medium">
                    {{ $hasAvailability ? 'Update' : 'Set' }} your weekly schedule so clients can easily see when you are available.
                </p>

                <div class="text-center border border-dashed border-gray-200 rounded-xl p-8 sm:p-10 bg-gray-50 mb-8">
                    <div class="max-w-xl mx-auto">
                        <div class="text-5xl mb-4">📅</div>

                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-3">
                            You {{ $hasAvailability ? 'have' : "haven't" }} set your availability {{ $hasAvailability ? '' : 'yet' }}
                        </h2>

                        <p class="text-gray-500 mb-6">
                            {{ $hasAvailability ? 'Update' : 'Set' }} your available days and times to start receiving better enquiries.
                        </p>

                        <button
                            type="button"
                            onclick="window.location.href='{{ route('availability.edit') }}'"
                            class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition"
                        >
                            {{ $hasAvailability ? 'Update' : 'Set' }} availability
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
