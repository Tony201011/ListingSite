@extends('layouts.frontend')

@section('content')
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" x-data="{ hasAvailability: false }">
        <div class="max-w-3xl mx-auto">

            <button
                onclick="window.history.back()"
                class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
            >
                <span class="mr-1">&lt;</span> Go back
            </button>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My availability</h2>
                    <p class="text-lg text-gray-600 mb-8 font-medium">
                        Set your weekly schedule so clients can easily see when you are available.
                    </p>

                    <div
                        x-show="!hasAvailability"
                        x-transition
                        class="text-center border border-dashed border-gray-200 rounded-xl p-8 sm:p-10 bg-gray-50 mb-8"
                    >
                        <div class="max-w-xl mx-auto">
                            <div class="text-5xl mb-4">📅</div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-3">You haven't set your availability yet</h2>
                            <p class="text-gray-500 mb-6">Add your available days and times to start receiving better enquiries.</p>

                            <button
                                type="button"
                                @click="hasAvailability = true"
                                class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5"
                            >
                                Set your availability
                            </button>
                        </div>
                    </div>

                    <div x-show="hasAvailability" x-transition class="max-w-xl mx-auto rounded-xl border border-pink-200 bg-pink-50 p-5 text-pink-700 text-center">
                        Availability setup started. You can now add your schedule details.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
