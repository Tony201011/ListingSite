@extends('layouts.frontend')

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
@php
    $verificationPhotos = is_array($lastTwoPhotos ?? null) ? $lastTwoPhotos : [];
@endphp

<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="verifyPage({
        uploadUrl: @js(route('photo-verification.upload')),
        deleteUrl: @js(route('photo-verification.delete-photo')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="mx-auto max-w-5xl space-y-6">
        <a
            href="{{ url('/profile-setting') }}"
            class="inline-flex items-center text-sm font-medium text-[#e04ecb] hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span>
            Back to profile settings
        </a>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                Verify your profile photos
            </h1>

            <p class="mt-3 text-gray-600">
                Photo verification is optional. Complete it to get a “Photos Verified” badge on your profile.
            </p>

            @if($latestVerification)
                <div
                    @class([
                        'mt-4 rounded-xl border p-4',
                        'border-yellow-200 bg-yellow-50' => $latestVerification->status === 'pending',
                        'border-green-200 bg-green-50' => $latestVerification->status === 'approved',
                        'border-red-200 bg-red-50' => $latestVerification->status === 'rejected',
                        'border-gray-200 bg-gray-50' => !in_array($latestVerification->status, ['pending', 'approved', 'rejected']),
                    ])
                >
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
                <p class="mt-1 font-medium text-pink-700">
                    your profile name * "Find me on Hotescorts.com.au" + today’s date
                </p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="mb-2 text-sm font-semibold text-gray-900">Photo 1</p>

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
                                    class="mb-3 h-80 w-full rounded-lg border-2 border-pink-300 object-cover"
                                >

                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    @if($photo1Status === 'approved')
                                        <span class="rotate-[-20deg] rounded-lg bg-green-600/80 px-4 py-2 text-xl font-extrabold text-white shadow-lg sm:text-2xl">
                                            PHOTO VERIFIED
                                        </span>
                                    @elseif($photo1Status === 'rejected')
                                        <span class="rotate-[-20deg] rounded-lg bg-red-600/80 px-4 py-2 text-xl font-extrabold text-white shadow-lg sm:text-2xl">
                                            REJECTED
                                        </span>
                                    @else
                                        <span class="rotate-[-20deg] rounded-lg bg-yellow-500/80 px-4 py-2 text-xl font-extrabold text-white shadow-lg sm:text-2xl">
                                            PHOTO NOT VERIFIED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute left-2 top-2">
                                    @if($photo1Status === 'approved')
                                        <span class="inline-flex items-center rounded-full bg-green-600 px-3 py-1 text-xs font-bold text-white shadow">
                                            Photo Verified
                                        </span>
                                    @elseif($photo1Status === 'rejected')
                                        <span class="inline-flex items-center rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white shadow">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-yellow-500 px-3 py-1 text-xs font-bold text-white shadow">
                                            Photo Not Verified
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($photo1Path) && $photo1Status !== 'approved')
                                    <button
                                        type="button"
                                        @click="deletePhoto(@js($photo1Path), 0)"
                                        class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 shadow hover:bg-red-50"
                                        title="Delete photo"
                                        aria-label="Delete photo 1"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M6 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm6-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            <path fill-rule="evenodd" d="M4 5a1 1 0 011-1h2.586A1 1 0 018.293 3.293l.414-.414A1 1 0 019.414 2h1.172a1 1 0 01.707.293l.414.414A1 1 0 0112.414 4H15a1 1 0 011 1v1H4V5zm1 3a1 1 0 011-1h8a1 1 0 011 1v8a2 2 0 01-2 2H7a2 2 0 01-2-2V8z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="mb-3 flex h-56 w-full items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-400">No uploaded photo yet</span>
                            </div>
                        @endif
                    </div>

                    <p class="text-sm text-gray-600">
                        Hold the note clearly in one hand. Your face or matching profile features must be visible.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="mb-2 text-sm font-semibold text-gray-900">Photo 2</p>

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
                                    class="mb-3 h-80 w-full rounded-lg border-2 border-pink-300 object-cover"
                                >

                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    @if($photo2Status === 'approved')
                                        <span class="rotate-[-20deg] rounded-lg bg-green-600/80 px-4 py-2 text-xl font-extrabold text-white shadow-lg sm:text-2xl">
                                            PHOTO VERIFIED
                                        </span>
                                    @elseif($photo2Status === 'rejected')
                                        <span class="rotate-[-20deg] rounded-lg bg-red-600/80 px-4 py-2 text-xl font-extrabold text-white shadow-lg sm:text-2xl">
                                            REJECTED
                                        </span>
                                    @else
                                        <span class="rotate-[-20deg] rounded-lg bg-yellow-500/80 px-4 py-2 text-xl font-extrabold text-white shadow-lg sm:text-2xl">
                                            PHOTO NOT VERIFIED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute left-2 top-2">
                                    @if($photo2Status === 'approved')
                                        <span class="inline-flex items-center rounded-full bg-green-600 px-3 py-1 text-xs font-bold text-white shadow">
                                            Photo Verified
                                        </span>
                                    @elseif($photo2Status === 'rejected')
                                        <span class="inline-flex items-center rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white shadow">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-yellow-500 px-3 py-1 text-xs font-bold text-white shadow">
                                            Photo Not Verified
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($photo2Path) && $photo2Status !== 'approved')
                                    <button
                                        type="button"
                                        @click="deletePhoto(@js($photo2Path), 1)"
                                        class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 shadow hover:bg-red-50"
                                        title="Delete photo"
                                        aria-label="Delete photo 2"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M6 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm6-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            <path fill-rule="evenodd" d="M4 5a1 1 0 011-1h2.586A1 1 0 018.293 3.293l.414-.414A1 1 0 019.414 2h1.172a1 1 0 01.707.293l.414.414A1 1 0 0112.414 4H15a1 1 0 011 1v1H4V5zm1 3a1 1 0 011-1h8a1 1 0 011 1v8a2 2 0 01-2 2H7a2 2 0 01-2-2V8z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="mb-3 flex h-56 w-full items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50">
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
                <a href="mailto:alice@hotescorts.com.au" class="font-medium text-pink-700 hover:text-pink-800">
                    alice@hotescorts.com.au
                </a>
            </p>

            <p class="mt-3 text-sm font-semibold text-pink-700">
                Profiles without verification can still be listed. Verification adds a “Photos Verified” badge.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button
                    type="button"
                    @click="openModal()"
                    class="rounded-lg bg-pink-600 px-6 py-3 font-semibold text-white transition hover:bg-pink-700"
                >
                    Upload photos for verified badge
                </button>

                <button
                    type="button"
                    onclick="window.history.back()"
                    class="rounded-lg border border-gray-200 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Back
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Example format</h2>

            <p class="mt-2 text-sm text-gray-600">
                Write exactly:
                <span class="font-semibold text-pink-700">
                    your profile name * "Find me on Hotescorts.com.au" + today’s date
                </span>
            </p>

            @if($exampleImages->count())
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($exampleImages as $exampleImage)
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                    <img
                        src="{{ $exampleImage->image_url }}"
                        alt="{{ $exampleImage->caption ?? 'Example ' . $loop->iteration }}"
                        class="h-80 w-full object-cover"
                    >
                    @if($exampleImage->caption)
                    <p class="px-3 py-2 text-xs text-gray-600">{{ $exampleImage->caption }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
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
                        <p class="text-lg font-semibold text-gray-700">Drag &amp; drop photos here</p>
                        <p class="mb-5 mt-1 text-sm text-gray-500">JPG, PNG, WEBP supported · max 5 photos · max 10MB each</p>

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
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="hidden"
                            @change="handleFileSelect($event)"
                        >
                    </div>

                    <template x-if="selectedFiles.length > 0">
                        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
                            <div class="mb-2 flex items-center justify-between">
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

                            <div class="max-h-48 space-y-2 overflow-y-auto">
                                <template x-for="(file, index) in selectedFiles" :key="file.name + file.size + index">
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm text-gray-700" x-text="file.name"></p>
                                            <p class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeSelectedFile(index)"
                                            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:bg-red-50"
                                            aria-label="Delete selected photo"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
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
                                    class="w-full rounded-lg bg-pink-600 px-6 py-3 font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                                >
                                    <span x-show="!isUploading" x-cloak>Upload selected photos</span>
                                    <span x-show="isUploading" x-cloak>Uploading...</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="activeTab === 'camera'" x-cloak x-transition>
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <video x-ref="video" autoplay playsinline muted class="max-h-72 w-full rounded-lg bg-gray-200"></video>
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

                        <template x-if="capturedImage">
                            <div class="mt-4">
                                <p class="mb-2 text-sm font-semibold text-gray-700">Captured preview</p>

                                <div class="relative inline-block">
                                    <button
                                        type="button"
                                        @click="clearCapturedPhoto()"
                                        class="absolute right-1.5 top-1.5 z-10 inline-flex h-6 w-6 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:bg-red-50"
                                        aria-label="Delete captured photo"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <img :src="capturedImage" alt="Captured photo" class="h-28 w-28 rounded-lg border-2 border-pink-300 object-cover">
                                </div>

                                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                                    <button
                                        type="button"
                                        @click="addCapturedPhotoToSelection()"
                                        class="w-full rounded-lg bg-pink-600 px-6 py-2.5 font-medium text-white transition hover:bg-pink-700 sm:w-auto"
                                    >
                                        Add captured photo
                                    </button>

                                    <button
                                        type="button"
                                        @click="clearCapturedPhoto()"
                                        class="w-full rounded-lg border border-gray-200 px-6 py-2.5 font-medium text-gray-700 transition hover:bg-gray-50 sm:w-auto"
                                    >
                                        Remove capture
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedFiles.length > 0">
                            <div class="mt-6 border-t border-gray-200 pt-4">
                                <p class="mb-3 text-sm font-semibold text-gray-700">
                                    Ready to upload: <span x-text="selectedFiles.length"></span> file(s)
                                </p>

                                <button
                                    type="button"
                                    @click="uploadFiles()"
                                    :disabled="isUploading || selectedFiles.length === 0"
                                    class="w-full rounded-lg bg-pink-600 px-6 py-3 font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                                >
                                    <span x-show="!isUploading" x-cloak>Upload selected photos</span>
                                    <span x-show="isUploading" x-cloak>Uploading...</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('profile/js/verify-page.js') }}?v={{ filemtime(public_path('profile/js/verify-page.js')) }}"></script>
@endpush
