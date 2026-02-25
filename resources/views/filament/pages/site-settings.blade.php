@extends('filament::page')

@section('content')
<div class="max-w-xl mx-auto mt-8">
    <form wire:submit.prevent="save">
        <div class="mb-4">
            <label for="meta_key" class="block text-sm font-medium">Meta Key</label>
            <input type="text" id="meta_key" wire:model="meta_key" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="mb-4">
            <label for="meta_description" class="block text-sm font-medium">Meta Description</label>
            <textarea id="meta_description" wire:model="meta_description" class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        @if(session('success'))
            <div class="mt-4 text-green-600">{{ session('success') }}</div>
        @endif
    </form>
</div>
@endsection
