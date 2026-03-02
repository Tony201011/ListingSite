@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{ enabled: false }">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Online now</h1>
            <p class="text-gray-600 mb-6">Promote online services in short visibility windows.</p>
            <button @click="enabled = !enabled" class="px-5 py-2.5 rounded-lg font-semibold transition" :class="enabled ? 'bg-pink-600 text-white hover:bg-pink-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" x-text="enabled ? 'Online now enabled' : 'Enable online now'"></button>
        </div>
    </div>
</div>
@endsection
