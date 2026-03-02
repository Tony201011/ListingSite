@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{ hidden: false }">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Hide profile</h1>
            <p class="text-gray-600 mb-6">Temporarily hide your profile from public listings and re-enable anytime.</p>
            <div class="rounded-xl border p-4 mb-6" :class="hidden ? 'border-pink-200 bg-pink-50' : 'border-gray-200 bg-gray-50'">
                <p class="font-semibold" :class="hidden ? 'text-pink-700' : 'text-gray-700'" x-text="hidden ? 'Profile is hidden' : 'Profile is visible'"></p>
            </div>
            <button @click="hidden = !hidden" class="px-6 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition" x-text="hidden ? 'Show profile' : 'Hide profile'"></button>
        </div>
    </div>
</div>
@endsection
