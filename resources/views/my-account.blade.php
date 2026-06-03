@extends('layouts.frontend')

@section('content')
@php
    $user = auth()->user();
    $displayName = old('name', $user->name ?? '');
    $email = $user->email ?? '';
    $mobile = old('mobile', $user->mobile ?? '');
@endphp

<div class="bg-white min-h-screen py-10 px-4" x-data="{}">
    <div class="max-w-4xl mx-auto">

        <button
            type="button"
            onclick="window.history.back()"
            class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8 border-l-[6px] border-[#e04ecb] pl-4">
            My Account
        </h1>

        {{-- Success message --}}
        <div x-data="{ show: true }">
            @if(session('success'))
                <div
                    x-show="show"
                    x-transition
                    class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                >
                    <span>{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-lg leading-none text-green-500 hover:text-green-700">&times;</button>
                </div>
            @endif
        </div>

        {{-- Error messages --}}
        <div x-data="{ show: true }">
            @if($errors->any())
                <div
                    x-show="show"
                    x-transition
                    class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    <div>
                        <p class="font-semibold mb-1">Please fix the following errors:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" @click="show = false" class="text-lg leading-none text-red-400 hover:text-red-600">&times;</button>
                </div>
            @endif
        </div>

        <div class="space-y-6">

            {{-- Account Information --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Account Information</h2>

                <form action="{{ route('my-account.update') }}" method="POST" class="space-y-6" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block font-semibold text-[#e04ecb] mb-1">Display Name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ $displayName }}"
                                placeholder="Your display name"
                                class="w-full px-4 py-3 border {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]' }} rounded-lg text-gray-900 font-medium focus:outline-none focus:ring-2 focus:border-transparent transition"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block font-semibold text-[#e04ecb] mb-1">Email Address</label>
                            <input
                                id="email"
                                type="email"
                                value="{{ $email }}"
                                readonly
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-500 font-medium bg-gray-100 cursor-not-allowed"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                To change your email, use
                                <a href="{{ url('/change-email') }}" class="text-[#e04ecb] hover:underline">Change Email</a>.
                            </p>
                        </div>
                    </div>

                    <div class="md:max-w-sm">
                        <label for="mobile" class="block font-semibold text-[#e04ecb] mb-1">Phone Number</label>
                        <input
                            id="mobile"
                            name="mobile"
                            type="tel"
                            value="{{ $mobile }}"
                            placeholder="e.g. +61 400 000 000"
                            class="w-full px-4 py-3 border {{ $errors->has('mobile') ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]' }} rounded-lg text-gray-900 font-medium focus:outline-none focus:ring-2 focus:border-transparent transition"
                        >
                        @error('mobile')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-semibold rounded-xl shadow-sm hover:shadow-md transform hover:-translate-y-0.5 transition focus:outline-none focus:ring-2 focus:ring-[#e04ecb] focus:ring-offset-2"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Password & Security --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Password &amp; Security</h2>
                <p class="text-sm text-gray-500 mb-6">Manage your password and email access settings.</p>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ url('/change-password') }}"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-xl transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#e04ecb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Change Password
                    </a>

                    <a
                        href="{{ url('/change-email') }}"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-xl transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#e04ecb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Change Email
                    </a>

                    @auth
                        @if (!auth()->user()->hasVerifiedEmail())
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-xl transition"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Verify Email
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-red-700 mb-2">Danger Zone</h2>
                <p class="text-sm text-red-600 mb-6">Permanently delete your account and all associated data. This action cannot be undone.</p>

                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="font-semibold text-red-700">Delete Account</h3>
                        <p class="text-sm text-red-500 mt-0.5">You will receive a confirmation email before deletion occurs.</p>
                    </div>

                    <a
                        href="{{ route('account.delete-page') }}"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Account
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
