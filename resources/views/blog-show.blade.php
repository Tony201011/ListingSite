@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between text-sm mb-4">
            <a href="{{ route('blog') }}" class="text-[#e04ecb] hover:text-[#c13ab0]">&lt;&lt; Blog index</a>
            @if($nextPost)
                <a href="{{ route('blog.show', $nextPost['slug']) }}" class="text-[#e04ecb] hover:text-[#c13ab0]">Next post &gt;&gt;</a>
            @endif
        </div>

        <article class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight mb-3">{{ $post['title'] }}</h1>

            <p class="text-base md:text-lg text-gray-700 mb-4">{{ $post['excerpt'] }}</p>
            <p class="text-sm text-gray-500 mb-6">Posted by {{ $post['author'] }} on {{ $post['date'] }}</p>

            @if(!empty($post['featured_image']))
                <div class="mb-6 overflow-hidden rounded-xl border border-gray-200">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($post['featured_image']) }}" alt="{{ $post['title'] }}" class="w-full h-auto">
                </div>
            @endif

            @if(!empty($post['featured_video']))
                <div class="mb-6 overflow-hidden rounded-xl border border-gray-200">
                    <video class="w-full" controls preload="metadata">
                        <source src="{{ \Illuminate\Support\Facades\Storage::url($post['featured_video']) }}">
                        Your browser does not support the video tag.
                    </video>
                </div>
            @endif

            <div class="space-y-4 text-gray-700 leading-relaxed [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_img]:rounded-lg [&_img]:w-full [&_a]:text-[#e04ecb] [&_a]:underline">
                {!! $post['content'] !!}
            </div>

            @if($post['slug'] === '24-7-billing-receiving-credit-card-payments')
                <div class="mt-6">
                    <a href="#" class="text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium">Download 24/7 billing app</a>
                    <div class="mt-3 overflow-hidden rounded-xl border border-gray-200">
                        <iframe class="w-full aspect-video" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="24/7 Billing" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs font-semibold text-pink-600">
                    <div class="bg-pink-50 border border-pink-100 rounded-lg py-3">FAST PAYMENTS</div>
                    <div class="bg-pink-50 border border-pink-100 rounded-lg py-3">TRUSTED SYSTEMS</div>
                    <div class="bg-pink-50 border border-pink-100 rounded-lg py-3">ANY INDUSTRY</div>
                    <div class="bg-pink-50 border border-pink-100 rounded-lg py-3">SIMPLE SETUP</div>
                </div>
            @endif
        </article>

        <div class="flex items-center justify-between text-sm mt-4">
            @if($previousPost)
                <a href="{{ route('blog.show', $previousPost['slug']) }}" class="text-[#e04ecb] hover:text-[#c13ab0]">&lt;&lt; Previous post</a>
            @else
                <span></span>
            @endif

            <a href="{{ route('blog') }}" class="text-gray-500 hover:text-gray-700">Back to all blogs</a>
        </div>
    </div>
</div>
@endsection
