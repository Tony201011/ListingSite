@extends('layouts.frontend')

@section('title', 'Terms and Conditions')

@section('content')
    @include('layouts.partials.content-section', [
        'title' => 'Terms and Conditions',
        'updatedAt' => $terms?->updated_at,
        'content' => $terms?->content,
        'emptyMessage' => 'Terms and conditions are not available yet.',
    ])
@endsection