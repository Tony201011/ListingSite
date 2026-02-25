@extends('layouts.frontend')

@section('title', 'Contact Us')

@section('content')
    @include('layouts.partials.content-section', [
        'title' => 'Contact Us',
        'contact' => $contact,
        'emptyMessage' => 'Contact are not available yet.',
    ])
@endsection
