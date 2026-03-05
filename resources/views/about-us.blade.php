@extends('layouts.frontend')

@section('title', 'About Us')

@section('content')
@php
    $pageTitle = $page?->title ?: 'About Us';
    $pageContent = $page?->content;
    $bannerTitle = $page?->banner_title ?: 'hotescorts.com.au';
    $bannerSubtitle = $page?->banner_subtitle ?: 'REAL WOMEN NEAR YOU';
    $defaultBannerImage = 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop';
    $bannerImage = filled($page?->banner_image_path)
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($page->banner_image_path)
        : $defaultBannerImage;
@endphp

<div class="relative overflow-hidden bg-gradient-to-r from-[#e04ecb] to-[#c13ab0]">
    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ $bannerImage }}');"></div>
    <div class="relative z-10 max-w-6xl mx-auto px-5 py-16 text-center">
        <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-2 drop-shadow-lg">{{ $bannerTitle }}</h1>
        <p class="text-xl text-white/90 tracking-widest">{{ $bannerSubtitle }}</p>
    </div>
</div>

<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $pageTitle }}</h1>
            <p class="mt-3 text-gray-600">Learn why providers and visitors choose our platform and what makes our directory different.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            @if(!empty($pageContent))
                <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700">
                    {!! $pageContent !!}
                </article>
            @else
                <div class="text-gray-500">About Us content is not available yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection
