@extends('layouts.frontend')

@section('content')
@php
    $user = auth()->user();
    $username = old('username', $user->username ?? $user->name ?? '');
    $displayName = old('name', $user->name ?? '');
    $email = old('email', $user->email ?? '');
    $phone = old('phone', $user->phone ?? $user->mobile ?? '');
@endphp

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-6 py-8">
    <div class="bg-white min-h-[600px]">
        <h1 class="text-3xl font-bold mb-8">My Account</h1>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Please fix the following errors:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Account Information -->
            <div class="border border-gray-300 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Account Information</h2>

                <form action="{{ url('/my-profile') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="{{ $username }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ $displayName }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ $email }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ $phone }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                    </div>

                    <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition">
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Password & Security -->
            <div class="border border-gray-300 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Password &amp; Security</h2>

                <form action="{{ route('change-password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            placeholder="Enter current password"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Enter new password"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                placeholder="Confirm new password"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                        </div>
                    </div>

                    <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition">
                        Update Password
                    </button>
                </form>
            </div>

            <!-- Notification Preferences -->
            <div class="border border-gray-300 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Notification Preferences</h2>

                <form action="{{ url('/my-profile/notifications') }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-medium">Email Notifications</h3>
                            <p class="text-sm text-gray-600">Receive updates about your listings via email</p>
                        </div>
                        <input name="email_notifications" type="checkbox" value="1" class="w-5 h-5 text-pink-500" checked>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-medium">Message Alerts</h3>
                            <p class="text-sm text-gray-600">Get notified when you receive new messages</p>
                        </div>
                        <input name="message_alerts" type="checkbox" value="1" class="w-5 h-5 text-pink-500" checked>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-medium">Marketing Emails</h3>
                            <p class="text-sm text-gray-600">Receive promotional offers and updates</p>
                        </div>
                        <input name="marketing_emails" type="checkbox" value="1" class="w-5 h-5 text-pink-500">
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-medium">Weekly Summary</h3>
                            <p class="text-sm text-gray-600">Get a weekly report of your listing performance</p>
                        </div>
                        <input name="weekly_summary" type="checkbox" value="1" class="w-5 h-5 text-pink-500" checked>
                    </div>

                    <button type="submit" class="mt-2 bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition">
                        Save Preferences
                    </button>
                </form>
            </div>

            <!-- Billing Information -->
            <div class="border border-gray-300 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Billing Information</h2>

                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">Current Plan</span>
                            <span class="text-pink-600 font-bold">{{ $currentPlan ?? 'Premium' }}</span>
                        </div>

                        <div class="flex justify-between items-center text-sm text-gray-600">
                            <span>Next billing date</span>
                            <span>{{ $nextBillingDate ?? 'July 1, 2026' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('payment-subscription') }}" class="inline-flex bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2 rounded transition">
                        Manage Subscription
                    </a>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="border border-red-300 rounded-lg p-6 bg-red-50">
                <h2 class="text-xl font-bold mb-4 text-red-700">Danger Zone</h2>

                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="font-medium text-red-700">Deactivate Account</h3>
                            <p class="text-sm text-red-600">Temporarily disable your account</p>
                        </div>

                        <form action="{{ url('/my-profile/deactivate') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-white border border-red-300 hover:bg-red-100 text-red-700 px-4 py-2 rounded transition">
                                Deactivate
                            </button>
                        </form>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="font-medium text-red-700">Delete Account</h3>
                            <p class="text-sm text-red-600">Permanently delete your account and all data</p>
                        </div>

                        <a href="{{ route('account.delete-page') }}" class="inline-flex bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">
                            Delete Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
