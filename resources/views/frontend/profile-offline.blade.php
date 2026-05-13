@extends('layouts.frontend')

@section('title', 'Escort Offline')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center bg-white rounded-2xl border border-gray-100 shadow-sm p-10">
        <div class="mb-6 flex justify-center">
            <span class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400">
                <i class="fas fa-user-slash text-3xl"></i>
            </span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-3">Escort is Offline</h1>
        <p class="text-gray-600 mb-8">
            The escort you are visiting is currently offline. Please check back later.
        </p>
        <a href="{{ url('/') }}"
           class="inline-block bg-pink-600 hover:bg-pink-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200">
            Browse Available Escorts
        </a>
    </div>
</div>
@endsection
