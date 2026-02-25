@extends('layouts.frontend')

@section('title', 'FAQ')

@section('content')
    @include('layouts.partials.content-section', [
        'title' => 'Frequently Asked Questions',
        'faqs' => $faqs,
        'emptyMessage' => 'FAQs are not available yet.',
    ])
@endsection