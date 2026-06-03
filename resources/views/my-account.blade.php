@extends('layouts.frontend')

@section('content')
@php
    $user = auth()->user();

    $username = old('username', $user->username ?? $user->name ?? '');
    $displayName = old('name', $user->name ?? '');
    $email = $user->email ?? '';
    $mobile = old('mobile', $user->mobile ?? '');

    $emailNotifications = old('email_notifications', $user->email_notifications ?? true);
    $messageAlerts = old('message_alerts', $user->message_alerts ?? true);
    $marketingEmails = old('marketing_emails', $user->marketing_emails ?? true);
    $weeklySummary = old('weekly_summary', $user->weekly_summary ?? true);

    $currentPlan = $user->plan_name ?? 'Premium';
    $renewalDate = optional($user->plan_expires_at ?? null)->format('M d, Y') ?? 'Auto renewal active';
@endphp

<div class="bg-white min-h-screen py-10 px-4" x-data="{}">
    <main class="max-w-7xl mx-auto px-6 py-8">    
        <div class="bg-white min-h-[600px]">

            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex items-center text-pink-500 hover:text-pink-600 transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <h1 class="text-3xl font-bold mb-8 text-gray-900">
                My Account
            </h1>

            {{-- Success message --}}
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

            {{-- Error messages --}}
            <div x-data="{ show: true }">
                @if($errors->any())
                    <div
                        x-show="show"
                        x-transition
                        class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
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
                <div class="border border-gray-300 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">Account Information</h2>

                    <form action="{{ route('my-account.update') }}" method="POST" class="space-y-4" autocomplete="off">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_section" value="account_information">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input
                                    id="username"
                                    name="username"
                                    type="text"
                                    value="{{ $username }}"
                                    class="w-full px-3 py-2 border {{ $errors->has('username') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }} rounded focus:outline-none focus:ring-2 text-gray-900"
                                >
                                @error('username')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ $displayName }}"
                                    class="w-full px-3 py-2 border {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }} rounded focus:outline-none focus:ring-2 text-gray-900"
                                >
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input
                                id="email"
                                type="email"
                                value="{{ $email }}"
                                readonly
                                class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none"
                            >
                        </div>

                        <div>
                            <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input
                                id="mobile"
                                name="mobile"
                                type="tel"
                                value="{{ $mobile }}"
                                class="w-full px-3 py-2 border {{ $errors->has('mobile') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }} rounded focus:outline-none focus:ring-2 text-gray-900"
                            >
                            @error('mobile')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition"
                        >
                            Save Changes
                        </button>
                    </form>
                </div>

                {{-- Password & Security --}}
                <div class="border border-gray-300 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">Password &amp; Security</h2>

                    <form action="{{ route('change-password.update') }}" method="POST" class="space-y-4" autocomplete="off">
                        @csrf

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                placeholder="Enter current password"
                                class="w-full px-3 py-2 border {{ $errors->has('current_password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }} rounded focus:outline-none focus:ring-2 text-gray-900"
                            >
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input
                                    id="new_password"
                                    name="new_password"
                                    type="password"
                                    placeholder="Enter new password"
                                    class="w-full px-3 py-2 border {{ $errors->has('new_password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }} rounded focus:outline-none focus:ring-2 text-gray-900"
                                >
                                @error('new_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input
                                    id="new_password_confirmation"
                                    name="new_password_confirmation"
                                    type="password"
                                    placeholder="Confirm new password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500 text-gray-900"
                                >
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition"
                        >
                            Update Password
                        </button>
                    </form>
                </div>

                {{-- Notification Preferences --}}
                <div class="border border-gray-300 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">Notification Preferences</h2>

                    <form action="{{ route('my-account.update') }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_section" value="notification_preferences">

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="font-medium text-gray-900">Email Notifications</h3>
                                <p class="text-sm text-gray-600">Receive updates about your listings via email</p>
                            </div>
                            <input type="hidden" name="email_notifications" value="0">
                            <input
                                type="checkbox"
                                name="email_notifications"
                                value="1"
                                class="w-5 h-5 text-pink-500"
                                @checked($emailNotifications)
                            >
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="font-medium text-gray-900">Message Alerts</h3>
                                <p class="text-sm text-gray-600">Get notified when someone sends you a message</p>
                            </div>
                            <input type="hidden" name="message_alerts" value="0">
                            <input
                                type="checkbox"
                                name="message_alerts"
                                value="1"
                                class="w-5 h-5 text-pink-500"
                                @checked($messageAlerts)
                            >
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="font-medium text-gray-900">Marketing Emails</h3>
                                <p class="text-sm text-gray-600">Receive promotions, feature updates, and special offers</p>
                            </div>
                            <input type="hidden" name="marketing_emails" value="0">
                            <input
                                type="checkbox"
                                name="marketing_emails"
                                value="1"
                                class="w-5 h-5 text-pink-500"
                                @checked($marketingEmails)
                            >
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="font-medium text-gray-900">Weekly Summary</h3>
                                <p class="text-sm text-gray-600">Get a weekly report of your listing performance</p>
                            </div>
                            <input type="hidden" name="weekly_summary" value="0">
                            <input
                                type="checkbox"
                                name="weekly_summary"
                                value="1"
                                class="w-5 h-5 text-pink-500"
                                @checked($weeklySummary)
                            >
                        </div>

                        <button
                            type="submit"
                            class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition"
                        >
                            Save Preferences
                        </button>
                    </form>
                </div>

                {{-- Billing Information --}}
                <div class="border border-gray-300 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">Billing Information</h2>

                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-900">Current Plan</span>
                                <span class="text-pink-600 font-bold">{{ $currentPlan }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Renewal</span>
                                <span class="text-sm text-gray-700">{{ $renewalDate }}</span>
                            </div>
                        </div>

                        <a
                            href="{{ url('/pricing') }}"
                            class="inline-block bg-pink-500 hover:bg-pink-600 text-white px-6 py-2 rounded transition"
                        >
                            Manage Plan
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
@endsection