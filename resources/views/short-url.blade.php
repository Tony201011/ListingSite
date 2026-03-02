@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{ slug: 'sourabh-wadhwa' }">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Short URL</h1>
            <p class="text-gray-600 mb-4">Set a clean URL that is easy to share on socials and messages.</p>
            <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden">
                <span class="px-3 py-2.5 bg-gray-50 text-gray-500 text-sm">hotescorts.com.au/</span>
                <input x-model="slug" class="flex-1 px-3 py-2.5 focus:outline-none">
            </div>
            <button class="mt-4 px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Save URL</button>
        </div>
    </div>
</div>
@endsection
