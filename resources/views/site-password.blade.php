@extends('layouts.frontend')

@section('content')
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#e04ecb] pl-4">Enter Site Password</h2>
        </div>
        <div class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">
            <form method="POST" action="/site-password">
                @csrf
                <div class="mb-6">
                    <label class="block font-semibold text-gray-800 mb-1">Password <span class="text-red-600">*</span></label>
                    <input type="password" name="password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition" placeholder="Enter Password" required>
                </div>
                @if(session('error'))
                    <div class="mb-4 text-red-600 font-semibold">{{ session('error') }}</div>
                @endif
                <button type="submit" class="w-full bg-[#e04ecb] hover:bg-[#c13ab0] text-white font-bold py-3 rounded-xl transition">Enter Website</button>
            </form>
        </div>
    </div>
</div>
@endsection
