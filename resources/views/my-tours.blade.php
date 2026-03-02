@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{ city:'', date:'' }">
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My tours</h1>
            <p class="text-gray-600 mb-6">Plan upcoming city tours so clients can pre-book in advance.</p>
            <div class="grid sm:grid-cols-2 gap-3">
                <input x-model="city" placeholder="City" class="px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input x-model="date" type="date" class="px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            </div>
            <button class="mt-4 px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Add tour</button>
        </div>
    </div>
</div>
@endsection
