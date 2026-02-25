@extends('layouts.frontend')

@section('title', 'Refund Policy')

@section('content')
    @include('layouts.partials.content-section', [
        'title' => 'Refund Policy',
        'updatedAt' => $policy?->updated_at,
        'content' => $policy?->content,
        'emptyMessage' => 'Refund policy is not available yet.',
    ])
@endsection