@extends('layouts.frontend')

@section('title', $page?->title ?: 'The Naughty Corner')

@section('content')
@php
    $pageTitle = $page?->title ?: 'The Naughty Corner';
    $pageSubtitle = $page?->subtitle ?: 'Webshop, coupons and handy tools';
    $pageContent = $page?->content;
@endphp

<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="mb-4">
            <a href="javascript:history.back()" class="inline-block border border-gray-300 rounded px-4 py-1 text-sm text-gray-600 hover:bg-gray-100">back</a>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $pageTitle }}</h1>
            <p class="mt-3 text-pink-600 font-medium">{{ $pageSubtitle }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            @if(!empty($pageContent))
                <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700 [&_hr]:border-gray-200 [&_hr]:my-6 [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-lg [&_img]:my-4">
                    {!! $pageContent !!}
                </article>
            @else
                <div class="text-gray-500">
                    Naughty Corner content is not available yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
