@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My videos</h1>
            <p class="text-gray-600 mb-6">Upload short preview clips to improve engagement and profile rank.</p>
            <button class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Upload video</button>
            <div class="mt-6 grid sm:grid-cols-2 gap-4">
                <div class="aspect-video rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">Video slot 1</div>
                <div class="aspect-video rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">Video slot 2</div>
            </div>
        </div>
    </div>
</div>
@endsection
