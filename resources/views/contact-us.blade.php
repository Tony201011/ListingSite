@extends('layouts.frontend')

@section('title', 'Contact Us')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Contact Us</h1>
            <p class="mt-3 text-gray-600">Have a question or need support? Send us a message and our team will get back to you.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <form class="space-y-4">
                    <input type="text" placeholder="Your name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <input type="email" placeholder="Your email" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <input type="text" placeholder="Subject" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <textarea rows="5" placeholder="Write your message..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent"></textarea>
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Send message</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-3">Support Info</h2>
                <p class="text-sm text-gray-600 mb-2">Response time: within 24 hours</p>
                <p class="text-sm text-gray-600 mb-2">Support email: support@hotescorts.com.au</p>
                <p class="text-sm text-gray-600">Category: {{ $contact ?? 'contact-us' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
