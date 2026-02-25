@extends('layouts.frontend')

@section('title', 'Privacy Policy')

@section('content')
    @include('layouts.partials.content-section', [
        'title' => 'Privacy Policy',
        'updatedAt' => $policy?->updated_at,
        'content' => $policy?->content,
        'emptyMessage' => 'Privacy policy is not available yet.',
    ])
@endsection