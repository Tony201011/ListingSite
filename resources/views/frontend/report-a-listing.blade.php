@extends('layouts.frontend')

@section('title', 'Report a Listing')

@section('content')
<div class="min-h-screen bg-gray-50">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
            <button
                type="button"
                onclick="window.history.back()"
                class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-pink-500 transition-colors hover:text-pink-600"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Report a Listing</h1>
                <p class="mt-3 text-gray-600">
                    Use this form to report a listing, image, profile, advertiser, review, or content that may violate our policies, contain misleading information, involve non-consensual content, impersonation, scams, or other prohibited activity.
                </p>
                <p class="mt-3 text-sm font-semibold text-red-600">
                    If someone is in immediate danger, call 000. This form is not monitored as an emergency service.
                </p>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <p class="font-semibold">{{ session('success') }}</p>
                    <p class="mt-2">We prioritise reports involving:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Inappropriate images</li>
                        <li>Non-consensual content</li>
                        <li>Underage concerns</li>
                        <li>Impersonation</li>
                        <li>Scams</li>
                        <li>Safety-related issues</li>
                    </ul>
                    <p class="mt-3">Our moderation team will review the report and take appropriate action.</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="mb-1 font-semibold">Please correct the errors below.</p>
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-lg border border-gray-300 p-6 lg:col-span-2">
                    <form action="{{ route('report-a-listing.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Report Category <span class="text-red-500">*</span></h2>
                            <select name="category" required class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                                @foreach($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </section>

                        <section class="space-y-4">
                            <h2 class="text-xl font-bold text-gray-900">Listing Information</h2>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Listing URL <span class="text-red-500">*</span></label>
                                    <input type="url" name="listing_url" required value="{{ old('listing_url', $prefill['listing_url'] ?? '') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500" placeholder="https://hotescort.com.au/...">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Profile / Advertiser Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="advertiser_name" required value="{{ old('advertiser_name', $prefill['advertiser_name'] ?? '') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Listing ID</label>
                                    <input type="text" name="listing_id" value="{{ old('listing_id', $prefill['listing_id'] ?? '') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Phone Number</label>
                                    <input type="text" name="listing_phone" value="{{ old('listing_phone') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">City / Location</label>
                                    <input type="text" name="listing_location" value="{{ old('listing_location') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                </div>
                            </div>
                        </section>

                        <section class="space-y-4">
                            <h2 class="text-xl font-bold text-gray-900">Reporter Information</h2>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Full Name</label>
                                    <input type="text" name="reporter_name" value="{{ old('reporter_name') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" name="reporter_email" required value="{{ old('reporter_email') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Phone Number</label>
                                    <input type="text" name="reporter_phone" value="{{ old('reporter_phone') }}" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous')) class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                Submit anonymously
                            </label>
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Report Details</h2>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Describe the issue <span class="text-red-500">*</span></label>
                            <textarea name="description" rows="6" required class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500" placeholder="Please explain what content you are reporting, why it violates our policies, and provide any relevant details that may assist our review.">{{ old('description') }}</textarea>
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Evidence Upload</h2>
                            <p class="text-sm text-gray-600">Upload screenshots, images, or supporting documents. Accepted formats: JPG, PNG, PDF (max 10MB per file).</p>
                            <input type="file" name="evidence[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Urgent Removal Request</h2>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_urgent" value="1" @checked(old('is_urgent')) class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                This content should be reviewed urgently
                            </label>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_person_shown" value="1" @checked(old('is_person_shown')) class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                I am the person shown in this listing/content
                            </label>
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Declaration</h2>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="declaration_accuracy" value="1" @checked(old('declaration_accuracy')) required class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                I confirm the information provided is accurate to the best of my knowledge.
                            </label>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="declaration_contact" value="1" @checked(old('declaration_contact')) required class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                I understand HotEscort may contact me for additional information regarding this report.
                            </label>
                        </section>

                        <button type="submit" class="rounded bg-pink-500 px-6 py-2 text-white transition hover:bg-pink-600">
                            Submit Report
                        </button>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="rounded-lg border border-gray-300 p-6">
                        <h2 class="mb-2 text-xl font-semibold text-gray-900">Helpful Links</h2>
                        <ul class="space-y-2 text-pink-600">
                            <li><a href="{{ route('terms-and-conditions') }}" class="hover:text-pink-700">Terms & Conditions</a></li>
                            <li><a href="{{ route('privacy-policy') }}" class="hover:text-pink-700">Privacy Policy</a></li>
                            <li><a href="{{ route('content-moderation-policy') }}" class="hover:text-pink-700">Content Moderation Policy</a></li>
                            <li><a href="{{ route('age-and-consent-policy') }}" class="hover:text-pink-700">Age & Consent Policy</a></li>
                            <li><a href="{{ route('prohibited-content-policy') }}" class="hover:text-pink-700">Prohibited Content & Services Policy</a></li>
                            <li><a href="{{ route('contact-us') }}" class="hover:text-pink-700">Contact Support</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
