@extends('layouts.frontend')

@section('content')
    <!-- Main Content - Profile Dashboard -->
    <!-- Using a light gray background for the page to make the white card pop (Modern UI) -->
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">

            <!-- Dashboard Title -->
            <!-- Added a subtle text shadow and improved typography -->
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-10 tracking-tight">
                Hotescorts dashboard
            </h1>

            <!-- Profile Setup Section Card -->
            <!-- Changed to a borderless/shadow style common in modern Aussie sites -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                <!-- Inner Padding -->
                <div class="p-6 sm:p-8">

                    <!-- Header Text -->
                    <p class="text-lg text-gray-600 mb-8 font-medium">
                        To set up your profile please do the next three steps:
                    </p>

                    <!-- Steps Container -->
                    <!-- Replaced Table with Flex/Grid for better Tailwind responsiveness -->
                    <div class="space-y-1 mb-10">

                        <!-- Table Header -->
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</span>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</span>
                        </div>

                        <!-- Step 1 -->
                        <div class="flex items-center justify-between py-4 hover:bg-gray-50 rounded-lg transition duration-150 ease-in-out px-2 -mx-2">
                            <div class="flex items-center">
                                <span class="text-lg font-semibold text-pink-600 mr-4">01</span>
                                <span class="text-gray-800 font-medium text-base">Write profile text</span>
                            </div>
                            <div class="ml-4">
                                <!-- Empty Circle Icon (SVG) -->
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white"></div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-center justify-between py-4 hover:bg-gray-50 rounded-lg transition duration-150 ease-in-out px-2 -mx-2">
                            <div class="flex items-center">
                                <span class="text-lg font-semibold text-pink-600 mr-4">02</span>
                                <span class="text-gray-800 font-medium text-base">Upload photos</span>
                            </div>
                            <div class="ml-4">
                                <!-- Empty Circle Icon (SVG) -->
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white"></div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-center justify-between py-4 hover:bg-gray-50 rounded-lg transition duration-150 ease-in-out px-2 -mx-2">
                            <div class="flex items-center">
                                <span class="text-lg font-semibold text-pink-600 mr-4">03</span>
                                <span class="text-gray-800 font-medium text-base">Verify your photos (optional badge)</span>
                            </div>
                            <div class="ml-4">
                                <!-- Empty Circle Icon (SVG) -->
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button Area -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <a href="{{ url('/my-profile-2') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5">
                            Start Writing Your Profile Text
                        </a>

                        <!-- Optional: Secondary link often found on these sites -->
                        <a href="{{ url('/after-image-upload') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                            or skip for now
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
