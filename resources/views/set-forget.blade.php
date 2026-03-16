@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{ autoMode: true }">
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Set & Forget</h1>
            <p class="text-gray-600 mb-6">Automate profile boosts and status updates at preferred times.</p>
            <div class="rounded-xl border border-gray-200 p-4 bg-gray-50 mb-4 flex items-center justify-between">
                <span class="text-gray-700 font-medium">Automation mode</span>
                <button @click="autoMode = !autoMode" class="px-4 py-2 rounded-lg text-sm font-semibold transition" :class="autoMode ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-700'" x-text="autoMode ? 'Enabled' : 'Disabled'"></button>
            </div>
            <button class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Save automation</button>
        </div>
    </div>
</div>
@endsection
