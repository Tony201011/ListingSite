@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="uploadVideoPage({
        uploadUrl: @js(route('videos.upload')),
        redirectUrl: @js(route('my-videos')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('my-videos') }}"
           class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to my videos
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Upload videos</h1>
            <p class="text-gray-600 mb-6">MP4/MOV up to 100MB.</p>

            <div
                class="border-2 border-dashed border-pink-200 p-6 text-center"
                :class="isDragging ? 'bg-pink-100' : ''"
                @dragenter.prevent="isDragging = true"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop($event)"
            >
                <input x-ref="input" type="file" multiple class="hidden"
                       @change="handleFiles($event)">

                <button @click="$refs.input.click()" class="bg-pink-600 text-white px-5 py-2 rounded">
                    Choose videos
                </button>

                <div class="mt-4" x-show="videos.length">
                    <p><span x-text="videos.length"></span> videos selected</p>

                    <button @click="uploadVideos()" class="bg-green-600 text-white px-4 py-2 mt-2">
                        Upload
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('profile/js/upload-video.js') }}"></script>
@endpush
