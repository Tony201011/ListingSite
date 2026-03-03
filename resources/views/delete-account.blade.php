@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/after-image-upload') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium">
            <span class="mr-1">&lt;</span> back to dashboard
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Delete Account</h1>
            <p class="text-sm text-gray-600 mb-6">This action is permanent. Once deleted, your profile and account data cannot be recovered.</p>

            <div class="rounded-xl border border-rose-100 bg-rose-50 p-4 mb-6">
                <p class="text-sm text-rose-700">Please contact support to confirm account deletion for security verification.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('contact-us') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-[#e04ecb] hover:bg-[#c13ab0] text-white text-sm font-semibold transition">Contact support</a>
                <a href="{{ url('/after-image-upload') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection
