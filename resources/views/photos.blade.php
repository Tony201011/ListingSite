@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My photos</h1>
                <a href="{{ url('/add-photo') }}" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Add photos</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @for($i=1;$i<=8;$i++)
                    <div class="aspect-[3/4] rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-400">Photo {{ $i }}</div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection
