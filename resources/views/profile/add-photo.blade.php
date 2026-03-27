@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-12 sm:px-6 lg:px-8"
    x-data="addPhotoPage({
        uploadUrl: @js(route('photos.upload')),
        photosUrl: @js(route('photos')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="mx-auto max-w-4xl">
        <button
            type="button"
            onclick="window.history.back()"
            class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-[#e04ecb] transition-colors hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span> back to profile
        </button>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="p-6 sm:p-8">
                <h1 class="mb-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Add photos to your profile
                </h1>

                <p class="mb-8 max-w-2xl text-base leading-relaxed text-gray-600 sm:text-lg">
                    Upload from your device, drag and drop multiple files, or take a photo directly with your camera.
                    Keep your gallery fresh to improve profile quality and visibility.
                </p>

                <div class="mb-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button
                        type="button"
                        @click="openModal()"
                        class="inline-flex w-full items-center justify-center rounded-full bg-pink-600 px-8 py-3 font-semibold text-white shadow-lg shadow-pink-600/20 transition hover:bg-pink-700 sm:w-auto"
                    >
                        Click to add photos
                    </button>

                    <a
                        href="{{ url('/after-image-upload') }}"
                        class="inline-flex w-full items-center justify-center rounded-full border border-pink-300 px-8 py-3 font-semibold text-pink-700 transition hover:bg-pink-50 sm:w-auto"
                    >
                        Continue setting up your profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="isModalOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="closeModal()"
    >
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center border-b border-gray-200 px-4 pt-4 sm:px-6">
                <button
                    type="button"
                    @click="switchTab('files')"
                    class="flex-1 border-b-2 pb-3 text-sm font-semibold transition sm:text-base"
                    :class="activeTab === 'files' ? 'border-pink-600 text-pink-600' : 'border-transparent text-gray-500'"
                >
                    My Files
                </button>

                <button
                    type="button"
                    @click="switchTab('camera')"
                    class="flex-1 border-b-2 pb-3 text-sm font-semibold transition sm:text-base"
                    :class="activeTab === 'camera' ? 'border-pink-600 text-pink-600' : 'border-transparent text-gray-500'"
                >
                    Camera
                </button>

                <button
                    type="button"
                    @click="closeModal()"
                    class="ml-4 text-2xl leading-none text-gray-500 hover:text-gray-700"
                    aria-label="Close modal"
                >
                    &times;
                </button>
            </div>

            <div class="bg-gray-50 p-4 sm:p-6">
                <div
                    x-show="successMessage"
                    x-cloak
                    x-transition
                    class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p x-text="successMessage"></p>
                        <button
                            type="button"
                            @click="successMessage = ''"
                            class="font-bold text-green-700 hover:text-green-900"
                            aria-label="Close success message"
                        >
                            &times;
                        </button>
                    </div>
                </div>

                <div
                    x-show="errorMessage"
                    x-cloak
                    x-transition
                    class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <p class="font-semibold">Upload failed</p>
                            <p class="whitespace-pre-line" x-text="errorMessage"></p>
                        </div>
                        <button
                            type="button"
                            @click="errorMessage = ''"
                            class="font-bold text-red-700 hover:text-red-900"
                            aria-label="Close error message"
                        >
                            &times;
                        </button>
                    </div>
                </div>

                <div x-show="activeTab === 'files'" x-cloak x-transition>
                    <div
                        class="rounded-xl border-2 border-dashed p-8 text-center transition sm:p-10"
                        :class="isDragging ? 'border-pink-400 bg-pink-50' : 'border-gray-300 bg-white'"
                        @dragenter.prevent="isDragging = true"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                    >
                        <div class="mb-4 text-5xl">📁</div>
                        <p class="text-lg font-semibold text-gray-700">Drag &amp; drop files here</p>
                        <p class="mb-5 mt-1 text-sm text-gray-500">JPG, PNG, WEBP supported</p>

                        <button
                            type="button"
                            @click="openFilePicker()"
                            class="inline-flex items-center rounded-lg bg-pink-600 px-6 py-2.5 font-medium text-white transition hover:bg-pink-700"
                        >
                            Browse files
                        </button>

                        <input
                            x-ref="fileInput"
                            type="file"
                            multiple
                            accept="image/*"
                            class="hidden"
                            @change="handleFileSelect($event)"
                        >
                    </div>

                    <template x-if="filePreviews.length > 0">
                        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-700">
                                    Selected (<span x-text="filePreviews.length"></span>)
                                </p>

                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="uploadFiles()"
                                        :disabled="uploading"
                                        class="flex items-center gap-1 rounded-full bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <svg x-show="!uploading" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>

                                        <svg x-show="uploading" class="h-3.5 w-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>

                                        <span x-text="uploading ? 'Uploading...' : 'Upload ' + filePreviews.length + ' file' + (filePreviews.length > 1 ? 's' : '')"></span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="clearSelectedFiles()"
                                        class="text-xs font-semibold text-red-600 hover:text-red-700"
                                    >
                                        Delete all
                                    </button>
                                </div>
                            </div>

                            <div class="grid max-h-60 grid-cols-3 gap-3 overflow-y-auto p-1 sm:grid-cols-4">
                                <template x-for="(preview, index) in filePreviews" :key="preview + '-' + index">
                                    <div
                                        class="group relative aspect-square cursor-pointer overflow-hidden rounded-lg border border-gray-200 bg-gray-100"
                                        @click="openSlider(index)"
                                    >
                                        <img :src="preview" :alt="'Preview ' + (index + 1)" class="h-full w-full object-cover">

                                        <button
                                            type="button"
                                            @click.stop="removeSelectedFile(index)"
                                            class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full border border-red-200 bg-white/90 text-red-600 opacity-0 transition-opacity hover:bg-red-50 group-hover:opacity-100"
                                            aria-label="Delete photo"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="activeTab === 'camera'" x-cloak x-transition>
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <video x-ref="video" autoplay playsinline class="max-h-72 w-full rounded-lg bg-gray-200"></video>
                        <canvas x-ref="canvas" class="hidden"></canvas>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <button
                                type="button"
                                @click="startCamera()"
                                class="w-full rounded-lg bg-pink-100 px-6 py-2.5 font-medium text-pink-700 transition hover:bg-pink-200 sm:w-auto"
                            >
                                Start camera
                            </button>

                            <button
                                type="button"
                                @click="capturePhoto()"
                                class="w-full rounded-lg bg-pink-600 px-6 py-2.5 font-medium text-white transition hover:bg-pink-700 sm:w-auto"
                            >
                                Capture
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="sliderOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4"
        @keydown.escape.window="closeSlider()"
        @keydown.left.window="prevSlide()"
        @keydown.right.window="nextSlide()"
    >
        <button
            type="button"
            @click="closeSlider()"
            class="absolute right-4 top-4 z-10 text-4xl leading-none text-white/80 hover:text-white"
            aria-label="Close preview"
        >
            &times;
        </button>

        <button
            type="button"
            @click="prevSlide()"
            class="absolute left-4 top-1/2 z-10 -translate-y-1/2 transform text-5xl leading-none text-white/80 hover:text-white"
            :class="{ 'cursor-not-allowed opacity-50': filePreviews.length <= 1 }"
            aria-label="Previous image"
        >
            &lsaquo;
        </button>

        <button
            type="button"
            @click="nextSlide()"
            class="absolute right-4 top-1/2 z-10 -translate-y-1/2 transform text-5xl leading-none text-white/80 hover:text-white"
            :class="{ 'cursor-not-allowed opacity-50': filePreviews.length <= 1 }"
            aria-label="Next image"
        >
            &rsaquo;
        </button>

        <template x-if="filePreviews.length > 0">
            <img
                :src="filePreviews[sliderIndex]"
                :alt="'Slide ' + (sliderIndex + 1)"
                class="max-h-full max-w-full rounded-lg object-contain"
            >
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/add-photo.js') }}"></script>
@endpush
