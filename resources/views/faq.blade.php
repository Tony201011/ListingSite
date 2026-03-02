@extends('layouts.frontend')

@section('title', 'FAQ')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Frequently Asked Questions</h1>
            <p class="mt-3 text-gray-600">Find quick answers about profile management, features, and safety.</p>
        </div>

        @if(!empty($faqs) && $faqs->isNotEmpty())
            <div x-data="{ openIndex: null }" class="space-y-3">
                @foreach($faqs as $index => $faq)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <button
                            type="button"
                            @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                            class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 hover:bg-pink-50 transition"
                        >
                            <span class="font-semibold text-gray-800">{{ $faq->question }}</span>
                            <span class="text-pink-600 text-xl leading-none" x-text="openIndex === {{ $index }} ? '−' : '+'"></span>
                        </button>

                        <div x-show="openIndex === {{ $index }}" x-collapse class="px-5 pb-5">
                            <div class="pt-2 border-t border-gray-100 text-gray-600 leading-relaxed prose max-w-none">
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-gray-500">
                FAQs are not available yet.
            </div>
        @endif
    </div>
</div>
@endsection
