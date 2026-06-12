@extends('layouts.frontend')

@section('title', 'Contact Us')

@section('content')
<div class="min-h-screen bg-gray-50">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8">
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

        <button
            type="button"
            onclick="window.history.back()"
            class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-pink-500 transition-colors hover:text-pink-600"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ $contactPage?->title ?? 'Contact Us' }}</h1>
            <p class="mt-3 text-gray-600">{{ $contactPage?->subtitle ?? 'Have a question or need support? Send us a message and our team will get back to you.' }}</p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="mb-1 font-semibold">Please fix the following errors:</p>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="rounded-lg border border-gray-300 p-6 lg:col-span-8">
                <form method="POST" action="{{ route('contact-us.submit') }}" class="space-y-4">
                    @csrf

                    @if($enableNameField)
                        <div>
                            <label for="contact-name" class="mb-1 block text-sm font-medium text-gray-700">Your name</label>
                            <input id="contact-name" type="text" name="name" value="{{ old('name') }}" placeholder="Your name" class="w-full rounded-md border px-3 py-2 text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }}">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($enableEmailField)
                        <div>
                            <label for="contact-email" class="mb-1 block text-sm font-medium text-gray-700">Your email</label>
                            <input id="contact-email" type="email" name="email" value="{{ old('email') }}" placeholder="Your email" class="w-full rounded-md border px-3 py-2 text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }}">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($enableSubjectField)
                        <div>
                            <label for="contact-subject" class="mb-1 block text-sm font-medium text-gray-700">Subject</label>
                            <input id="contact-subject" type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject" class="w-full rounded-md border px-3 py-2 text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 {{ $errors->has('subject') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }}">
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($enableMessageField)
                        <div>
                            <label for="contact-message" class="mb-1 block text-sm font-medium text-gray-700">Message</label>
                            <textarea id="contact-message" rows="6" name="message" placeholder="Write your message..." class="w-full rounded-md border px-3 py-2 text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 {{ $errors->has('message') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-pink-500' }}">{{ old('message') }}</textarea>
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

                    <button type="submit" @disabled(! $canSubmitForm) class="inline-flex w-full items-center justify-center rounded-md bg-pink-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-600 sm:w-auto disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600">Send message</button>
                </form>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="rounded-lg border border-gray-300 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $contactPage?->support_heading ?? 'Support Info' }}</h2>
                    <dl class="space-y-3 text-sm text-gray-600">
                        <div>
                            <dt class="font-medium text-gray-700">Response time</dt>
                            <dd>{{ $contactPage?->response_time ?? 'within 24 hours' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Support email</dt>
                            <dd class="break-all">{{ $contactEmail ?? 'support@hotescorts.com.au' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Category</dt>
                            <dd>{{ $contactPage?->category_label ?? 'contact-us' }}</dd>
                        </div>
                    </dl>
                </div>

                @if($showMap)
                    <div class="rounded-lg border border-gray-300 p-3">
                        <iframe
                            src="{{ $mapSrc }}"
                            class="h-64 w-full rounded border border-gray-200"
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
    </main>
</div>
@endsection
