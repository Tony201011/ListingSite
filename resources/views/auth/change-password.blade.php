@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{}">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8">
            <button
                type="button"
                onclick="window.history.back()"
                class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-pink-500 transition-colors hover:text-pink-600"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <h1 class="mb-8 text-3xl font-bold text-gray-900">
                Change Password
            </h1>

            <div x-data="{ show: true }">
                @if(session('success'))
                    <div
                        x-show="show"
                        x-transition
                        class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                    >
                        <span>{{ session('success') }}</span>
                        <button type="button" @click="show = false" class="text-lg leading-none text-green-500 hover:text-green-700">&times;</button>
                    </div>
                @endif
            </div>

            <div x-data="{ show: true }">
                @if($errors->any())
                    <div
                        x-show="show"
                        x-transition
                        class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    >
                        <div>
                            <p class="mb-1 font-semibold">Please fix the following errors:</p>
                            <ul class="list-disc space-y-1 pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" @click="show = false" class="text-lg leading-none text-red-400 hover:text-red-600">&times;</button>
                    </div>
                @endif
            </div>

            <div class="max-w-3xl">
                <div class="rounded-lg border border-gray-300 p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900">Password &amp; Security</h2>

                    <form action="{{ route('change-password.update') }}" method="POST" class="space-y-4" autocomplete="off">
                        @csrf

                        <div>
                            <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700">Current Password</label>
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                placeholder="Enter current password"
                                class="w-full rounded border px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 {{ $errors->has('current_password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }}"
                            >
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="new_password" class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                                <input
                                    id="new_password"
                                    name="new_password"
                                    type="password"
                                    placeholder="Enter new password"
                                    class="w-full rounded border px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 {{ $errors->has('new_password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }}"
                                >
                                @error('new_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <input
                                    id="new_password_confirmation"
                                    name="new_password_confirmation"
                                    type="password"
                                    placeholder="Confirm new password"
                                    class="w-full rounded border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-pink-500"
                                >
                            </div>
                        </div>

                        <button type="submit" class="rounded bg-pink-500 px-6 py-2 text-white transition hover:bg-pink-600">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
