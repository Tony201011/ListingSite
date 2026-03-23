@extends('layouts.frontend')

@section('content')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@php
    $verificationPhotos = is_array($lastTwoPhotos ?? null) ? $lastTwoPhotos : [];
@endphp

<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="verifyPage({
        uploadUrl: '{{ route('photo-verification.upload') }}',
        deleteUrl: '{{ route('photo-verification.delete-photo') }}',
        csrfToken: '{{ csrf_token() }}'
    })"
>
    <div class="max-w-5xl mx-auto space-y-6">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">
                Verify your profile photos
            </h1>

            <p class="mt-3 text-gray-600">
                Photo verification is optional. Complete it to get a “Photos Verified” badge on your profile.
            </p>

            @if($latestVerification)
                <div class="mt-4 rounded-xl border p-4
                    @class([
                        'border-yellow-200 bg-yellow-50' => $latestVerification->status === 'pending',
                        'border-green-200 bg-green-50' => $latestVerification->status === 'approved',
                        'border-red-200 bg-red-50' => $latestVerification->status === 'rejected',
                        'border-gray-200 bg-gray-50' => !in_array($latestVerification->status, ['pending', 'approved', 'rejected']),
                    ])
                ">
                    <p class="text-sm font-semibold text-gray-900">
                        Latest verification status:
                        <span class="uppercase">{{ $latestVerification->status }}</span>
                    </p>
                    @if($latestVerification->submitted_at)
                        <p class="mt-1 text-sm text-gray-600">
                            Submitted on {{ \Carbon\Carbon::parse($latestVerification->submitted_at)->format('d M Y, h:i A') }}
                        </p>
                    @endif
                </div>
            @endif

            <div class="mt-6 rounded-xl border border-pink-100 bg-pink-50 p-4">
                <p class="text-sm font-semibold text-pink-800">Verification note format</p>
                <p class="mt-1 text-pink-700 font-medium">
                    your profile name * "Find me on Hotescorts.com.au" + today’s date
                </p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-sm font-semibold text-gray-900 mb-2">Photo 1</p>

                    <div class="mt-2">
                        @if(!empty($verificationPhotos[0]['url']))
                            @php
                                $photo1Status = strtolower($verificationPhotos[0]['status'] ?? '');
                                $photo1Path = $verificationPhotos[0]['path'] ?? '';
                            @endphp

                            <div class="relative">
                                <img
                                    src="{{ $verificationPhotos[0]['url'] }}"
                                    alt="Uploaded verification photo 1"
                                    class="w-full h-100 rounded-lg border-2 border-pink-300 mb-3"
                                >

                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    @if($photo1Status === 'approved')
                                        <span class="rotate-[-20deg] text-white text-xl sm:text-2xl font-extrabold bg-green-600/80 px-4 py-2 rounded-lg shadow-lg">
                                            PHOTO VERIFIED
                                        </span>
                                    @elseif($photo1Status === 'rejected')
                                        <span class="rotate-[-20deg] text-white text-xl sm:text-2xl font-extrabold bg-red-600/80 px-4 py-2 rounded-lg shadow-lg">
                                            REJECTED
                                        </span>
                                    @else
                                        <span class="rotate-[-20deg] text-white text-xl sm:text-2xl font-extrabold bg-yellow-500/80 px-4 py-2 rounded-lg shadow-lg">
                                            PHOTO NOT VERIFIED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2 left-2">
                                    @if($photo1Status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-600 text-white shadow">
                                            Photo Verified
                                        </span>
                                    @elseif($photo1Status === 'rejected')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-600 text-white shadow">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-500 text-white shadow">
                                            Photo Not Verified
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($photo1Path) && $photo1Status !== 'approved')
                                    <button
                                        type="button"
                                        @click="deletePhoto('{{ addslashes($photo1Path) }}', 0)"
                                        class="absolute top-2 right-2 h-8 w-8 inline-flex items-center justify-center rounded-full bg-white border border-red-200 text-red-600 hover:bg-red-50 shadow"
                                        title="Delete photo"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M6 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm6-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            <path fill-rule="evenodd" d="M4 5a1 1 0 011-1h2.586A1 1 0 018.293 3.293l.414-.414A1 1 0 019.414 2h1.172a1 1 0 01.707.293l.414.414A1 1 0 0112.414 4H15a1 1 0 011 1v1H4V5zm1 3a1 1 0 011-1h8a1 1 0 011 1v8a2 2 0 01-2 2H7a2 2 0 01-2-2V8z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="w-full h-56 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center mb-3">
                                <span class="text-sm text-gray-400">No uploaded photo yet</span>
                            </div>
                        @endif
                    </div>

                    <p class="text-sm text-gray-600">
                        Hold the note clearly in one hand. Your face or matching profile features must be visible.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-sm font-semibold text-gray-900 mb-2">Photo 2</p>

                    <div class="mt-2">
                        @if(!empty($verificationPhotos[1]['url']))
                            @php
                                $photo2Status = strtolower($verificationPhotos[1]['status'] ?? '');
                                $photo2Path = $verificationPhotos[1]['path'] ?? '';
                            @endphp

                            <div class="relative">
                                <img
                                    src="{{ $verificationPhotos[1]['url'] }}"
                                    alt="Uploaded verification photo 2"
                                    class="w-full h-100 object-cover rounded-lg border-2 border-pink-300 mb-3"
                                >

                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    @if($photo2Status === 'approved')
                                        <span class="rotate-[-20deg] text-white text-xl sm:text-2xl font-extrabold bg-green-600/80 px-4 py-2 rounded-lg shadow-lg">
                                            PHOTO VERIFIED
                                        </span>
                                    @elseif($photo2Status === 'rejected')
                                        <span class="rotate-[-20deg] text-white text-xl sm:text-2xl font-extrabold bg-red-600/80 px-4 py-2 rounded-lg shadow-lg">
                                            REJECTED
                                        </span>
                                    @else
                                        <span class="rotate-[-20deg] text-white text-xl sm:text-2xl font-extrabold bg-yellow-500/80 px-4 py-2 rounded-lg shadow-lg">
                                            PHOTO NOT VERIFIED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2 left-2">
                                    @if($photo2Status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-600 text-white shadow">
                                            Photo Verified
                                        </span>
                                    @elseif($photo2Status === 'rejected')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-600 text-white shadow">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-500 text-white shadow">
                                            Photo Not Verified
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($photo2Path))
                                    <button
                                        type="button"
                                        @click="deletePhoto('{{ addslashes($photo2Path) }}', 1)"
                                        class="absolute top-2 right-2 h-8 w-8 inline-flex items-center justify-center rounded-full bg-white border border-red-200 text-red-600 hover:bg-red-50 shadow"
                                        title="Delete photo"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M6 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm6-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            <path fill-rule="evenodd" d="M4 5a1 1 0 011-1h2.586A1 1 0 018.293 3.293l.414-.414A1 1 0 019.414 2h1.172a1 1 0 01.707.293l.414.414A1 1 0 0112.414 4H15a1 1 0 011 1v1H4V5zm1 3a1 1 0 011-1h8a1 1 0 011 1v8a2 2 0 01-2 2H7a2 2 0 01-2-2V8z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="w-full h-56 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center mb-3">
                                <span class="text-sm text-gray-400">No uploaded photo yet</span>
                            </div>
                        @endif
                    </div>

                    <p class="text-sm text-gray-600">
                        Use the same note, crumple it slightly, and hold it in your other hand while keeping text readable.
                    </p>
                </div>
            </div>

            <p class="mt-6 text-sm text-gray-600">
                We do <span class="font-semibold">not</span> publish verification photos. If needed, you can also contact support:
                <a href="mailto:alice@hotescorts.com.au" class="text-pink-700 hover:text-pink-800 font-medium">
                    alice@hotescorts.com.au
                </a>
            </p>

            <p class="mt-3 text-sm font-semibold text-pink-700">
                Profiles without verification can still be listed. Verification adds a “Photos Verified” badge.
            </p>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button
                    type="button"
                    @click="openModal()"
                    class="px-6 py-3 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition"
                >
                    Upload photos for verified badge
                </button>

                <button
                    type="button"
                    onclick="window.history.back()"
                    class="px-6 py-3 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold transition"
                >
                    Back
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900">Example format</h2>

            <p class="mt-2 text-sm text-gray-600">
                Write exactly:
                <span class="text-pink-700 font-semibold">
                    your profile name * "Find me on Hotescorts.com.au" + today’s date
                </span>
            </p>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                    <img
                        src="https://pub-4e37ec8f58e94a569d35a5245489f90d.r2.dev/verification/badge_dummy_image/badge-fummy-image.png"
                        alt="Example 1"
                        class="w-full h-44 object-cover"
                    >
                    <p class="px-3 py-2 text-xs text-gray-600">Example 1: clear note + visible face</p>
                </div>

                <div class="rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                    <img
                        src="https://pub-4e37ec8f58e94a569d35a5245489f90d.r2.dev/verification/badge_dummy_image/same-note-in-another-hand.png"
                        alt="Example 2"
                        class="w-full h-44 object-cover"
                    >
                    <p class="px-3 py-2 text-xs text-gray-600">Example 2: same note in other hand</p>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="isModalOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
        @click.self="closeModal()"
    >
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center border-b border-gray-200 px-4 sm:px-6 pt-4">
                <button
                    type="button"
                    @click="switchTab('files')"
                    class="flex-1 pb-3 text-sm sm:text-base font-semibold border-b-2 transition"
                    :class="activeTab === 'files' ? 'text-pink-600 border-pink-600' : 'text-gray-500 border-transparent'"
                >
                    My Files
                </button>

                <button
                    type="button"
                    @click="switchTab('camera')"
                    class="flex-1 pb-3 text-sm sm:text-base font-semibold border-b-2 transition"
                    :class="activeTab === 'camera' ? 'text-pink-600 border-pink-600' : 'text-gray-500 border-transparent'"
                >
                    Camera
                </button>

                <button
                    type="button"
                    @click="closeModal()"
                    class="ml-4 text-gray-500 hover:text-gray-700 text-2xl leading-none"
                >
                    &times;
                </button>
            </div>

            <div class="p-4 sm:p-6 bg-gray-50">
                <template x-if="uploadSuccessMessage">
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" x-text="uploadSuccessMessage"></div>
                </template>

                <template x-if="uploadErrorMessage">
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="uploadErrorMessage"></div>
                </template>

                <div x-show="activeTab === 'files'" x-transition>
                    <div
                        class="border-2 border-dashed rounded-xl p-8 sm:p-10 text-center transition"
                        :class="isDragging ? 'border-pink-400 bg-pink-50' : 'border-gray-300 bg-white'"
                        @dragenter.prevent="isDragging = true"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                    >
                        <div class="text-5xl mb-4">📁</div>
                        <p class="text-lg font-semibold text-gray-700">Drag & drop photos here</p>
                        <p class="text-sm text-gray-500 mt-1 mb-5">JPG, PNG, WEBP supported · max 5 photos · max 10MB each</p>

                        <button
                            type="button"
                            @click="openFilePicker()"
                            class="inline-flex items-center px-6 py-2.5 rounded-lg text-white font-medium bg-pink-600 hover:bg-pink-700 transition"
                        >
                            Browse files
                        </button>

                        <input
                            x-ref="fileInput"
                            type="file"
                            multiple
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="hidden"
                            @change="handleFileSelect($event)"
                        >
                    </div>

                    <template x-if="selectedFiles.length > 0">
                        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-700">
                                    Selected files (<span x-text="selectedFiles.length"></span>/5)
                                </p>

                                <button
                                    type="button"
                                    @click="clearSelectedFiles()"
                                    class="text-xs font-semibold text-red-600 hover:text-red-700"
                                >
                                    Delete all
                                </button>
                            </div>

                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                <template x-for="(file, index) in selectedFiles" :key="file.name + file.size + index">
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2">
                                        <div class="min-w-0">
                                            <p class="text-sm text-gray-700 truncate" x-text="file.name"></p>
                                            <p class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeSelectedFile(index)"
                                            class="h-6 w-6 shrink-0 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition"
                                            aria-label="Delete selected photo"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <button
                                    type="button"
                                    @click="uploadFiles()"
                                    :disabled="isUploading || selectedFiles.length === 0"
                                    class="w-full sm:w-auto px-6 py-3 rounded-lg bg-pink-600 hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold transition"
                                >
                                    <span x-show="!isUploading">Upload selected photos</span>
                                    <span x-show="isUploading">Uploading...</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="activeTab === 'camera'" x-transition>
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <video x-ref="video" autoplay playsinline muted class="w-full max-h-72 rounded-lg bg-gray-200"></video>
                        <canvas x-ref="canvas" class="hidden"></canvas>

                        <div class="mt-4 flex flex-col sm:flex-row gap-3">
                            <button
                                type="button"
                                @click="startCamera()"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-pink-100 text-pink-700 font-medium hover:bg-pink-200 transition"
                            >
                                Start camera
                            </button>

                            <button
                                type="button"
                                @click="capturePhoto()"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-pink-600 text-white font-medium hover:bg-pink-700 transition"
                            >
                                Capture
                            </button>
                        </div>

                        <template x-if="capturedImage">
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Captured preview</p>

                                <div class="relative inline-block">
                                    <button
                                        type="button"
                                        @click="clearCapturedPhoto()"
                                        class="absolute top-1.5 right-1.5 z-10 h-6 w-6 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition"
                                        aria-label="Delete captured photo"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <img :src="capturedImage" alt="Captured photo" class="w-28 h-28 object-cover rounded-lg border-2 border-pink-300">
                                </div>

                                <div class="mt-4 flex flex-col sm:flex-row gap-3">
                                    <button
                                        type="button"
                                        @click="addCapturedPhotoToSelection()"
                                        class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-pink-600 text-white font-medium hover:bg-pink-700 transition"
                                    >
                                        Add captured photo
                                    </button>

                                    <button
                                        type="button"
                                        @click="clearCapturedPhoto()"
                                        class="w-full sm:w-auto px-6 py-2.5 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition"
                                    >
                                        Remove capture
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedFiles.length > 0">
                            <div class="mt-6 border-t border-gray-200 pt-4">
                                <p class="text-sm font-semibold text-gray-700 mb-3">
                                    Ready to upload: <span x-text="selectedFiles.length"></span> file(s)
                                </p>

                                <button
                                    type="button"
                                    @click="uploadFiles()"
                                    :disabled="isUploading || selectedFiles.length === 0"
                                    class="w-full sm:w-auto px-6 py-3 rounded-lg bg-pink-600 hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold transition"
                                >
                                    <span x-show="!isUploading">Upload selected photos</span>
                                    <span x-show="isUploading">Uploading...</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function verifyPage(config) {
        return {
            uploadUrl: config.uploadUrl,
            deleteUrl: config.deleteUrl,
            csrfToken: config.csrfToken,

            isModalOpen: false,
            activeTab: 'files',
            isDragging: false,
            selectedFiles: [],
            capturedImage: '',
            stream: null,
            isUploading: false,
            uploadSuccessMessage: '',
            uploadErrorMessage: '',

            openModal() {
                this.isModalOpen = true;
                this.uploadSuccessMessage = '';
                this.uploadErrorMessage = '';
            },

            closeModal() {
                this.isModalOpen = false;
                this.isDragging = false;
                this.stopCamera();
                this.uploadSuccessMessage = '';
                this.uploadErrorMessage = '';
            },

            switchTab(tab) {
                this.activeTab = tab;

                if (tab === 'camera') {
                    this.startCamera();
                } else {
                    this.stopCamera();
                }
            },

            openFilePicker() {
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.click();
                }
            },

            handleFileSelect(event) {
                const files = Array.from(event.target.files || []);
                this.addFiles(files);

                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            handleDrop(event) {
                this.isDragging = false;
                const files = Array.from(event.dataTransfer.files || []);
                this.addFiles(files);
            },

            addFiles(files) {
                this.uploadSuccessMessage = '';
                this.uploadErrorMessage = '';

                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                const existingKeys = new Set(this.selectedFiles.map(file => `${file.name}-${file.size}-${file.lastModified || 0}`));

                for (const file of files) {
                    if (!allowedTypes.includes(file.type)) {
                        continue;
                    }

                    const key = `${file.name}-${file.size}-${file.lastModified || 0}`;
                    if (existingKeys.has(key)) {
                        continue;
                    }

                    if (this.selectedFiles.length >= 5) {
                        this.uploadErrorMessage = 'You can upload a maximum of 5 photos.';
                        break;
                    }

                    this.selectedFiles.push(file);
                    existingKeys.add(key);
                }
            },

            removeSelectedFile(index) {
                this.selectedFiles.splice(index, 1);

                if (!this.selectedFiles.length && this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            clearSelectedFiles() {
                this.selectedFiles = [];

                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            async startCamera() {
                if (this.stream) {
                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Camera is not supported on this device or browser.');
                    return;
                }

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        }
                    });

                    if (this.$refs.video) {
                        this.$refs.video.srcObject = this.stream;
                    }
                } catch (error) {
                    alert('Camera access denied or not available.');
                }
            },

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach((track) => track.stop());
                    this.stream = null;
                }

                if (this.$refs.video) {
                    this.$refs.video.srcObject = null;
                }
            },

            capturePhoto() {
                const videoElement = this.$refs.video;
                const canvasElement = this.$refs.canvas;

                if (!videoElement || !canvasElement || !videoElement.videoWidth) {
                    return;
                }

                canvasElement.width = videoElement.videoWidth;
                canvasElement.height = videoElement.videoHeight;

                const context = canvasElement.getContext('2d');
                context.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
                this.capturedImage = canvasElement.toDataURL('image/png');
            },

            dataURLtoFile(dataUrl, filename) {
                const arr = dataUrl.split(',');
                const mimeMatch = arr[0].match(/:(.*?);/);
                const mime = mimeMatch ? mimeMatch[1] : 'image/png';
                const bstr = atob(arr[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);

                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }

                return new File([u8arr], filename, { type: mime });
            },

            addCapturedPhotoToSelection() {
                if (!this.capturedImage) {
                    return;
                }

                if (this.selectedFiles.length >= 5) {
                    this.uploadErrorMessage = 'You can upload a maximum of 5 photos.';
                    return;
                }

                const fileName = `camera-capture-${Date.now()}.png`;
                const file = this.dataURLtoFile(this.capturedImage, fileName);
                this.selectedFiles.push(file);
                this.uploadSuccessMessage = 'Captured photo added to upload list.';
                this.uploadErrorMessage = '';
            },

            clearCapturedPhoto() {
                this.capturedImage = '';
            },

            formatFileSize(bytes) {
                if (bytes < 1024) return `${bytes} B`;
                if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
                return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
            },

            async uploadFiles() {
                if (!this.selectedFiles.length) {
                    this.uploadErrorMessage = 'Please select at least one photo.';
                    this.uploadSuccessMessage = '';
                    return;
                }

                this.isUploading = true;
                this.uploadErrorMessage = '';
                this.uploadSuccessMessage = '';

                const formData = new FormData();
                this.selectedFiles.forEach((file) => {
                    formData.append('photos[]', file);
                });

                try {
                    const response = await fetch(this.uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (data.errors) {
                            const firstErrorGroup = Object.values(data.errors)[0];
                            const firstError = Array.isArray(firstErrorGroup) ? firstErrorGroup[0] : data.message;
                            throw new Error(firstError || 'Upload failed.');
                        }

                        throw new Error(data.message || 'Upload failed.');
                    }

                    this.uploadSuccessMessage = data.message || 'Verification photos uploaded successfully.';
                    this.selectedFiles = [];
                    this.capturedImage = '';
                    this.stopCamera();

                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } catch (error) {
                    this.uploadErrorMessage = error.message || 'Something went wrong while uploading.';
                } finally {
                    this.isUploading = false;
                }
            },

            async deletePhoto(path, index) {
                if (!path) {
                    return;
                }

                const confirmed = confirm('Are you sure you want to delete this photo?');
                if (!confirmed) {
                    return;
                }

                this.uploadSuccessMessage = '';
                this.uploadErrorMessage = '';

                try {
                    const response = await fetch(this.deleteUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ path, index })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Photo delete failed.');
                    }

                    this.uploadSuccessMessage = data.message || 'Photo deleted successfully.';

                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } catch (error) {
                    this.uploadErrorMessage = error.message || 'Something went wrong while deleting the photo.';
                }
            }
        };
    }
</script>
@endsection
