@extends('layouts.frontend')

@section('title', 'Anti Spam Policy')

@section('content')
    @include('layouts.partials.content-section', [
        'title' => 'Anti Spam Policy',
        'updatedAt' => $policy?->updated_at,
        'content' => $policy?->content,
        'emptyMessage' => 'Anti spam policy is not available yet.',
    ])
@endsection