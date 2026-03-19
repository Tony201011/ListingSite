@extends('layouts.frontend')

@section('content')
    @php
        $hasAvailability = $availabilityCount > 0;
    @endphp

    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">

            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
            >
                <span class="mr-1">&lt;</span> Go back
            </button>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My Availability</h2>

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
                                class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5"
                            >
                                {{ $hasAvailability ? 'Update' : 'Set' }} availability
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
