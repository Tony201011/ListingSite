@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My Babe Rank</h1>
            <p class="text-gray-600 mb-6">Track your current performance and top actions to improve ranking.</p>
            <div class="grid sm:grid-cols-3 gap-3 mb-6">
                <div class="rounded-xl border border-pink-200 bg-pink-50 p-4 text-center">
                    <p class="text-sm text-pink-700">Current rank</p>
                    <p class="text-3xl font-bold text-pink-700">7</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                    <p class="text-sm text-gray-600">Profile score</p>
                    <p class="text-3xl font-bold text-gray-800">82%</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                    <p class="text-sm text-gray-600">Views today</p>
                    <p class="text-3xl font-bold text-gray-800">156</p>
                </div>
            </div>
            <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
                <li>Add fresh photos weekly</li>
                <li>Update availability daily</li>
                <li>Keep rates and profile message current</li>
            </ul>
        </div>
    </div>
</div>
@endsection
