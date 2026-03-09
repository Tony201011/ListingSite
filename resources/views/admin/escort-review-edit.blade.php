@extends('layouts.admin')

@section('title', 'Edit Escort Review Page')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-pink-600 mb-6">Edit Escort Review Page</h1>
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.escort-review.update') }}">
        @csrf
        <div class="mb-6">
            <label for="content" class="block text-lg font-semibold mb-2">Page Content</label>
            <textarea id="content" name="content" rows="12" class="w-full border rounded p-3">{{ old('content', $escortReviewPage->content ?? '') }}</textarea>
        </div>
        <button type="submit" class="px-6 py-2 rounded bg-pink-600 text-white font-bold hover:bg-pink-700 transition">Save Changes</button>
    </form>
</div>
@endsection
