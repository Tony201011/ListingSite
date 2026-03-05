@extends('layouts.frontend')

@section('title', 'Help')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $page?->title ?: 'Help' }}</h1>
            <p class="mt-3 text-gray-600">{{ $page?->subtitle ?: 'Find quick support links for account, profile, and billing related questions.' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            @if(!empty($page?->content))
                <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700">
                    {!! $page->content !!}
                </article>
            @else
                <div class="space-y-6 text-gray-700">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Popular Help Topics</h2>
                        <ul class="list-disc pl-6 space-y-1 text-gray-600">
                            <li>How to create and verify your profile</li>
                            <li>How to update photos, rates, and availability</li>
                            <li>How credits and pricing packages work</li>
                            <li>How to hide, pause, or reactivate your listing</li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Need detailed answers?</h2>
                        <p class="text-gray-600">Visit our FAQ page for complete answers to common questions.</p>
                        <a href="{{ route('faq') }}" class="mt-3 inline-flex rounded-md bg-pink-500 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-600">Go to FAQ</a>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Still need help?</h2>
                        <p class="text-gray-600">Contact our support team and we’ll assist you as soon as possible.</p>
                        <a href="{{ route('contact-us') }}" class="mt-3 inline-flex rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Contact Support</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
