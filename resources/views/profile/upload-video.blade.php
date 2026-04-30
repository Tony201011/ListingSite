@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="uploadVideoPage({
        uploadUrl: @js(route('videos.upload')),
        redirectUrl: @js(route('my-videos')),
        csrfToken: @js(csrf_token()),
        maxUploadMb: @js(\App\Models\SiteSetting::getMaxVideoUploadMb())
    })"
>
    <div class="mx-auto max-w-4xl">
        <a
            href="{{ route('my-videos') }}"
            class="mb-4 inline-flex items-center text-sm font-medium text-[#e04ecb] hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span>
            Back to my videos
        </a>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="mb-2 text-2xl font-bold text-gray-900 sm:text-3xl">
                Upload videos
            </h1>

            <p class="mb-6 text-gray-600">
                Add short clips for your profile. MP4/MOV up to <span x-text="maxUploadMb"></span>MB each.
            </p>

            <template x-if="successMessage">
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <div class="flex items-start justify-between gap-3">
                        <p x-text="successMessage"></p>
                        <button
                            type="button"
                            @click="successMessage = ''"
                            class="font-bold text-green-700 hover:text-green-900"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="errorMessage">
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="flex items-start justify-between gap-3">
                        <p class="whitespace-pre-line" x-text="errorMessage"></p>
                        <button
                            type="button"
                            @click="errorMessage = ''"
                            class="font-bold text-red-700 hover:text-red-900"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            </template>

            <div
                class="rounded-xl border-2 border-dashed border-pink-200 bg-pink-50/50 p-6 text-center"
                :class="isDragging ? 'border-pink-400 bg-pink-100/60' : ''"
                @dragenter.prevent="isDragging = true"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop($event)"
            >
                <input
                    x-ref="videoInput"
                    type="file"
                    multiple
                    accept="video/mp4,video/quicktime,video/*"
                    class="hidden"
                    @change="handleVideoChange($event)"
                >

                <template x-if="!selectedVideos.length">
                    <div>
                        <p class="mb-4 text-sm text-gray-600">
                            Drag &amp; drop multiple videos here or click to choose files.
                        </p>

                        <button
                            type="button"
                            @click="$refs.videoInput.click()"
                            class="rounded-lg bg-pink-600 px-5 py-2.5 font-semibold text-white transition hover:bg-pink-700"
                        >
                            Choose video files
                        </button>
                    </div>
                </template>

                <template x-if="selectedVideos.length">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700">
                                Selected (<span x-text="selectedVideos.length"></span>)
                            </p>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="uploadVideos()"
                                    :disabled="uploading"
                                    class="rounded-full bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span x-text="uploading ? 'Uploading...' : 'Upload ' + selectedVideos.length + ' video' + (selectedVideos.length > 1 ? 's' : '')"></span>
                                </button>

                                <button
                                    type="button"
                                    @click="clearSelection()"
                                    class="text-xs font-semibold text-red-600 hover:text-red-700"
                                >
                                    Delete all
                                </button>
                            </div>
                        </div>

                        <div class="grid max-h-[28rem] grid-cols-1 gap-4 overflow-y-auto p-1 sm:grid-cols-2">
                            <template x-for="(video, index) in selectedVideos" :key="video.key">
                                <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white">
                                    <button
                                        type="button"
                                        @click="removeSelectedVideo(index)"
                                        class="absolute right-1.5 top-1.5 z-10 inline-flex h-7 w-7 items-center justify-center rounded-full border border-red-200 bg-white/95 text-red-600 transition hover:bg-red-50"
                                        aria-label="Delete selected video"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <video :src="video.previewUrl" controls preload="none" class="aspect-video w-full bg-black object-cover"></video>

                                    <div class="p-3 text-left">
                                        <p class="break-all text-sm font-medium text-gray-700" x-text="video.name"></p>
                                        <p class="mt-1 text-xs text-gray-500" x-text="formatFileSize(video.size)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-center gap-2">
                            <button
                                type="button"
                                @click="$refs.videoInput.click()"
                                class="rounded-lg border border-pink-200 px-4 py-2 text-sm font-semibold text-pink-700 transition hover:bg-pink-50"
                            >
                                Add more videos
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex items-center gap-2">
                <button
                    type="button"
                    @click="uploadVideos()"
                    :disabled="uploading || !selectedVideos.length"
                    class="rounded-lg bg-pink-600 px-5 py-2.5 font-semibold text-white transition hover:bg-pink-700 disabled:opacity-50"
                >
                    <span x-text="uploading ? 'Uploading...' : 'Upload now'"></span>
                </button>

                <a
                    href="{{ route('my-videos') }}"
                    class="rounded-lg border border-gray-200 px-5 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('profile/js/upload-video.js') }}"></script>
@endpush
