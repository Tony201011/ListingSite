@extends('layouts.frontend')

@section('title', 'Premium Directory | Home')

@section('content')

    <header class="relative py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-6">Find Your Perfect <span class="text-purple-500">Companion</span></h1>

            <div class="bg-gray-800 p-2 rounded-2xl shadow-2xl flex flex-col md:flex-row gap-2 border border-gray-700">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" placeholder="Enter Location..." class="w-full bg-gray-900 border-none rounded-xl py-4 pl-12 focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
                <div class="flex-1 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" placeholder="I am looking for..." class="w-full bg-gray-900 border-none rounded-xl py-4 pl-12 focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
                <button type="button" onclick="window.location='{{ url('/provider/content-listings') }}'" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-8 rounded-xl transition duration-200">
                    Search
                </button>
            </div>

            <div class="flex flex-wrap justify-center gap-3 mt-8">
                <a href="{{ url('/provider/content-listings') }}" class="bg-gray-800 px-4 py-2 rounded-full text-sm border border-gray-700 cursor-pointer hover:border-purple-500 transition">Sydney</a>
                <a href="{{ url('/provider/content-listings') }}" class="bg-gray-800 px-4 py-2 rounded-full text-sm border border-gray-700 cursor-pointer hover:border-purple-500 transition">Melbourne</a>
                <a href="{{ url('/provider/content-listings') }}" class="bg-gray-800 px-4 py-2 rounded-full text-sm border border-gray-700 cursor-pointer hover:border-purple-500 transition">Brisbane</a>
                <a href="{{ route('faq') }}" class="bg-gray-800 px-4 py-2 rounded-full text-sm border border-gray-700 cursor-pointer hover:border-purple-500 transition text-purple-400 font-bold border-purple-500/50">View All Locations</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-12">

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-bold">Featured <span class="text-pink-500">Babes</span></h2>
                <p class="text-gray-400 mt-1">Our most popular verified providers this week.</p>
            </div>
            <div class="flex items-center gap-2 bg-gray-800 p-1 rounded-lg border border-gray-700">
                <span class="text-xs font-bold px-2">AVAILABLE NOW</span>
                <button x-data="{ on: false }" @click="on = !on" :class="on ? 'bg-green-500' : 'bg-gray-600'" class="w-10 h-5 rounded-full relative transition duration-200">
                    <span :class="on ? 'translate-x-5' : 'translate-x-1'" class="absolute top-1 left-0 w-3 h-3 bg-white rounded-full transition duration-200"></span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

            <div class="group bg-gray-800 rounded-2xl overflow-hidden border border-gray-700 hover:border-purple-500 transition-all duration-300 shadow-lg hover:-translate-y-1">
                <div class="relative aspect-[3/4]">
                    <img src="https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?q=80&w=600" alt="Profile" class="w-full h-full object-cover">
                    <div class="absolute top-3 right-3 bg-green-500 text-white text-[10px] font-extrabold px-2 py-1 rounded shadow-lg animate-pulse">
                        LIVE NOW
                    </div>
                    <div class="absolute top-3 left-3 bg-purple-600 text-white text-[10px] font-extrabold px-2 py-1 rounded shadow-lg">
                        VERIFIED
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-gray-900 to-transparent">
                        <h3 class="text-xl font-bold">Sophia, 24</h3>
                        <p class="text-sm text-gray-300"><i class="fa-solid fa-location-dot mr-1 text-purple-400"></i> Sydney CBD</p>
                    </div>
                </div>
                <div class="p-4 flex justify-between items-center bg-gray-800/50">
                    <span class="text-lg font-bold text-purple-400">$350<span class="text-xs text-gray-400">/hr</span></span>
                    <a href="{{ url('/provider/content-listings') }}" class="bg-gray-700 hover:bg-purple-600 p-2 rounded-lg transition">
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="group bg-gray-800 rounded-2xl overflow-hidden border border-gray-700 hover:border-purple-500 transition-all duration-300 shadow-lg hover:-translate-y-1">
                <div class="relative aspect-[3/4]">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=600" alt="Profile" class="w-full h-full object-cover">
                    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-gray-900 to-transparent">
                        <h3 class="text-xl font-bold">Elena, 22</h3>
                        <p class="text-sm text-gray-300"><i class="fa-solid fa-location-dot mr-1 text-purple-400"></i> Melbourne</p>
                    </div>
                </div>
                <div class="p-4 flex justify-between items-center">
                    <span class="text-lg font-bold text-purple-400">$400<span class="text-xs text-gray-400">/hr</span></span>
                    <a href="{{ url('/provider/content-listings') }}" class="bg-gray-700 hover:bg-purple-600 p-2 rounded-lg transition">
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gray-800 rounded-2xl aspect-[3/4] border border-gray-700 border-dashed flex items-center justify-center">
                <span class="text-gray-600">Sample Listing</span>
            </div>
            <div class="bg-gray-800 rounded-2xl aspect-[3/4] border border-gray-700 border-dashed flex items-center justify-center">
                <span class="text-gray-600">Sample Listing</span>
            </div>

        </div>

        <div class="text-center mt-12">
            <a href="{{ url('/provider/content-listings') }}" class="inline-block bg-transparent border border-gray-700 hover:border-purple-500 px-12 py-3 rounded-full font-bold transition">
                Load More Babes
            </a>
        </div>
    </main>

    <section class="bg-gray-800/30 border-y border-gray-800 py-16 mt-12 text-center">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <p class="text-4xl font-bold text-purple-500">2,500+</p>
                <p class="text-gray-400">Active Listings</p>
            </div>
            <div>
                <p class="text-4xl font-bold text-purple-500">100%</p>
                <p class="text-gray-400">Verified Photos</p>
            </div>
            <div>
                <p class="text-4xl font-bold text-purple-500">24/7</p>
                <p class="text-gray-400">Customer Support</p>
            </div>
            <div>
                <p class="text-4xl font-bold text-purple-500">45,000</p>
                <p class="text-gray-400">Weekly Visitors</p>
            </div>
        </div>
    </section>

@endsection
