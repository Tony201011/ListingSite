@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/after-image-upload') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium">
            <span class="mr-1">&lt;</span> Back to dashboard
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Delete Account</h1>
            <p class="text-sm text-gray-600 mb-6">
                This action is permanent. Once deleted, your profile, photos, videos, and account data cannot be recovered.
            </p>

            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('account.destroy') }}" method="POST" class="space-y-5">
                @csrf
                @method('DELETE')

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirm your password
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div>
                    <label for="confirmation_text" class="block text-sm font-semibold text-gray-700 mb-2">
                        Type <span class="text-red-600 font-bold">DELETE</span> to confirm
                    </label>
                    <input
                        type="text"
                        name="confirmation_text"
                        id="confirmation_text"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                        placeholder="Type DELETE"
                        required
                    >
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Deleting your account will permanently remove all your account data, uploaded images, videos, and profile information.
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition"
                    >
                        Delete my account permanently
                    </button>

                    <a href="{{ route('contact-us') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-[#e04ecb] hover:bg-[#c13ab0] text-white text-sm font-semibold transition">
                        Contact support
                    </a>

                    <a href="{{ url('/after-image-upload') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
