@extends('layouts.frontend')

@section('title', 'Anti Spam Policy')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Anti Spam Policy</h1>
            @if($policy?->updated_at)
                <p class="mt-3 text-sm text-gray-500">Last updated: {{ $policy->updated_at->format('d M Y') }}</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            @if(!empty($policy?->content))
                <div class="prose prose-gray max-w-none prose-headings:text-gray-900 prose-a:text-pink-600 hover:prose-a:text-pink-700">
                    {!! $policy->content !!}
                </div>
            @else
                <p class="text-gray-500">Anti Spam Policy is not available yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
