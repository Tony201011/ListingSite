@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        @include('profile.partials.back-to-settings')
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My Babe Rank</h1>
            <p class="text-gray-600 mb-6">Track your current performance and top actions to improve ranking.</p>
            <div class="grid sm:grid-cols-3 gap-3 mb-6">
                <div class="rounded-xl border border-pink-200 bg-pink-50 p-4 text-center">
                    <p class="text-sm text-pink-700">Current rank</p>
                    <p class="text-3xl font-bold text-pink-700">{{ $rank }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                    <p class="text-sm text-gray-600">Profile score</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $profileScore }}%</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                    <p class="text-sm text-gray-600">Views today</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $viewsToday }}</p>
                </div>
            </div>

            @if($shortCode)
            <div class="mb-6 rounded-xl border border-pink-200 bg-pink-50 p-4">
                <p class="text-sm font-medium text-pink-700 mb-1">Your Short Code</p>
                <p class="text-lg font-bold text-pink-700 break-all">{{ $shortCode }}</p>
                <p class="text-xs text-pink-600 mt-1">Share this code with clients: <span class="font-semibold">{{ url($shortCode) }}</span></p>
            </div>
            @else
            <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-sm text-gray-600">You have not set a short code yet. <a href="{{ url('/short-url') }}" class="text-pink-600 hover:underline font-medium">Set your short URL</a> to boost your rank.</p>
            </div>
            @endif

            <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
                <li>Add fresh photos weekly</li>
                <li>Update availability daily</li>
                <li>Keep rates and profile message current</li>
            </ul>

            <div class="mt-6">
                <a href="{{ route('babe-rank-read-more') }}" class="text-sm font-medium text-pink-600 hover:underline">
                    Read more about how Babe Rank works &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
