@extends('layouts.frontend')

@section('title', 'Contact Us')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        @php
            $enableNameField = $contactPage?->enable_name_field ?? true;
            $enableEmailField = $contactPage?->enable_email_field ?? true;
            $enableSubjectField = $contactPage?->enable_subject_field ?? true;
            $enableMessageField = $contactPage?->enable_message_field ?? true;
            $canSubmitForm = $enableNameField || $enableEmailField || $enableSubjectField || $enableMessageField;

            $mapLatitude = $contactPage?->map_latitude;
            $mapLongitude = $contactPage?->map_longitude;
            $showMap = ($contactPage?->enable_map ?? false) && $mapLatitude !== null && $mapLongitude !== null;
            $mapSrc = $showMap
                ? 'https://maps.google.com/maps?q=' . $mapLatitude . ',' . $mapLongitude . '&z=15&output=embed'
                : null;
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $contactPage?->title ?? 'Contact Us' }}</h1>
            <p class="mt-3 text-gray-600">{{ $contactPage?->subtitle ?? 'Have a question or need support? Send us a message and our team will get back to you.' }}</p>
        </div>

        @if(session('success'))
            <div class="bg-white rounded-xl border border-green-200 shadow-sm px-5 py-4 mb-6 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <form method="POST" action="{{ route('contact-us.submit') }}" class="space-y-4">
                    @csrf

                    @if($enableNameField)
                        <div>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($enableEmailField)
                        <div>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Your email" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($enableSubjectField)
                        <div>
                            <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($enableMessageField)
                        <div>
                            <textarea rows="5" name="message" placeholder="Write your message..." class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @unless($canSubmitForm)
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                            Contact form is currently disabled by admin settings.
                        </div>
                    @endunless

                    <button type="submit" @disabled(! $canSubmitForm) class="px-6 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition disabled:bg-gray-300 disabled:text-gray-600 disabled:cursor-not-allowed">Send message</button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">{{ $contactPage?->support_heading ?? 'Support Info' }}</h2>
                    <p class="text-sm text-gray-600 mb-2">Response time: {{ $contactPage?->response_time ?? 'within 24 hours' }}</p>
                    <p class="text-sm text-gray-600 mb-2">Support email: {{ $contactEmail ?? 'support@hotescorts.com.au' }}</p>
                    <p class="text-sm text-gray-600">Category: {{ $contactPage?->category_label ?? 'contact-us' }}</p>
                </div>

                @if($showMap)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3">
                        <iframe
                            src="{{ $mapSrc }}"
                            class="w-full h-64 rounded-xl border border-gray-100"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            title="Location map"
                        ></iframe>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
