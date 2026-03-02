@extends('layouts.frontend')

@section('title', 'Refund Policy')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Refund Policy</h1>
            @if($policy?->updated_at)
                <p class="mt-3 text-sm text-gray-500">Last updated: {{ $policy->updated_at->format('M d, Y') }}</p>
            @endif
        </div>

        @if(!empty($policy?->content))
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <article class="prose max-w-none text-gray-700 leading-relaxed">
                    {!! $policy->content !!}
                </article>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-gray-500">
                Refund policy is not available yet.
            </div>
        @endif
    </div>
</div>
@endsection
