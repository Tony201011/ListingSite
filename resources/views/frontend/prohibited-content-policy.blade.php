@extends('layouts.frontend')

@section('title', 'Prohibited Content/Services Policy')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{}">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8">
            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex items-center text-pink-500 hover:text-pink-600 transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <h1 class="text-3xl font-bold text-gray-900">Prohibited Content/Services Policy</h1>
            <p class="mt-2 text-gray-600 {{ $policy?->updated_at ? 'mb-2' : 'mb-8' }}">Understand what content and services are not permitted on this platform.</p>
            @if($policy?->updated_at)
                <p class="mb-8 text-sm text-gray-500">Last updated: {{ $policy->updated_at->format('M d, Y') }}</p>
            @endif

            <div class="border border-gray-300 rounded-lg p-6">
                @if(!empty($policy?->content))
                    <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700 [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-lg [&_img]:my-4">
                        {!! $policy->content !!}
                    </article>
                @else
                    <p class="text-gray-500">Prohibited content/services policy is not available yet.</p>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection
