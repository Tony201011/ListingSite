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
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $pageTitle }}</h1>
            <p class="mt-3 text-pink-600 text-lg font-semibold">{{ $pageSubtitle }}</p>
            <p class="mt-2 text-gray-600">Who doesn’t like discounts? Discover trusted stores and useful resources in one place.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            @if(!empty($pageContent))
                <article class="text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700 [&_img]:rounded-lg [&_img]:my-4">
                    {!! $pageContent !!}
                </article>
            @else
                <div class="space-y-6 text-gray-700">
                    <div class="pb-6 border-b border-gray-200">
                        <h2 class="text-2xl font-semibold text-sky-500">Love Honey - www.lovehoney.com.au</h2>
                        <p class="mt-2 text-gray-600">Lovehoney are the sexual happiness people and offer expert chat and support to help you shop with confidence.</p>
                        <p class="mt-2"><strong>Love Honey lingerie offer:</strong> <a href="#" class="text-sky-500">Buy 1 Get 1 Half Price</a></p>
                    </div>

                    <div class="pb-6 border-b border-gray-200">
                        <h2 class="text-2xl font-semibold text-sky-500">Wild Secrets - www.wildsecrets.com.au</h2>
                        <p class="mt-2 text-gray-600">Explore toys, lingerie, costumes and more with discreet delivery and a great range of offers.</p>
                        <p class="mt-2"><strong>Offer:</strong> Buy one get one free with code <strong>WILDFREE</strong>.</p>
                    </div>

                    <div class="pb-2">
                        <h2 class="text-2xl font-semibold text-sky-500">More recommended stores</h2>
                        <ul class="mt-3 list-disc pl-6 space-y-1 text-gray-600">
                            <li>JouJou - www.joujou.com.au</li>
                            <li>FemPlay - www.femplay.com.au</li>
                            <li>Club X - www.clubx.com.au</li>
                            <li>Adult Shop - www.adultshop.com.au</li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
