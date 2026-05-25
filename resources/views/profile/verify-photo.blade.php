@extends('layouts.frontend')

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
        @include('profile.partials.back-to-settings')

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-gray-900">
                Verify your profile photos
            </h1>

            <p class="mt-3 text-xs sm:text-base text-gray-600">
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
                                    loading="lazy"
                                    decoding="async"
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
                            {{-- Interactive drag-and-drop zone for Photo 1 --}}
                            <div
                                class="relative mb-3 rounded-lg border-2 border-dashed transition-colors duration-150 cursor-pointer"
                                :class="isDraggingSlot1 ? 'border-pink-400 bg-pink-50' : 'border-gray-300 bg-gray-50 hover:border-pink-300 hover:bg-pink-50/50'"
                                @click="openSlotFilePicker(1)"
                                @dragenter.prevent="isDraggingSlot1 = true"
                                @dragover.prevent="isDraggingSlot1 = true"
                                @dragleave.prevent="if (!$el.contains($event.relatedTarget)) isDraggingSlot1 = false"
                                @drop.prevent="handleSlotDrop($event, 1)"
                                role="button"
                                tabindex="0"
                                @keydown.enter.prevent="openSlotFilePicker(1)"
                                @keydown.space.prevent="openSlotFilePicker(1)"
                                aria-label="Upload Photo 1 — drag and drop or press Enter to browse"
                            >
                                <template x-if="previewUrl1">
                                    <div class="relative">
                                        <img
                                            :src="previewUrl1"
                                            alt="Photo 1 preview"
                                            class="h-56 w-full rounded-lg object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <div class="pointer-events-none absolute inset-0 flex items-end justify-center rounded-lg pb-2">
                                            <span class="rounded-full bg-black/50 px-3 py-1 text-xs font-medium text-white">
                                                Click or drag to replace
                                            </span>
                                        </div>
                                        <button
                                            type="button"
                                            @click.stop="removeSlotFile(1)"
                                            class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 shadow hover:bg-red-50"
                                            aria-label="Remove Photo 1"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="!previewUrl1">
                                    <div class="flex h-56 flex-col items-center justify-center p-4 text-center pointer-events-none">
                                        <svg
                                            class="mb-3 h-10 w-10 transition-colors"
                                            :class="isDraggingSlot1 ? 'text-pink-400' : 'text-gray-300'"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                        </svg>
                                        <p
                                            class="text-sm font-medium transition-colors"
                                            :class="isDraggingSlot1 ? 'text-pink-600' : 'text-gray-600'"
                                            x-text="isDraggingSlot1 ? 'Drop photo here' : 'Drag & drop or click to browse'"
                                        ></p>
                                        <p class="mt-1 text-xs text-gray-400">JPG, PNG, WEBP · max 10 MB</p>
                                    </div>
                                </template>
                                <input
                                    x-ref="slotFileInput1"
                                    type="file"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    class="hidden"
                                    @change="handleSlotFileSelect($event, 1)"
                                >
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
                                    loading="lazy"
                                    decoding="async"
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
                            {{-- Interactive drag-and-drop zone for Photo 2 --}}
                            <div
                                class="relative mb-3 rounded-lg border-2 border-dashed transition-colors duration-150 cursor-pointer"
                                :class="isDraggingSlot2 ? 'border-pink-400 bg-pink-50' : 'border-gray-300 bg-gray-50 hover:border-pink-300 hover:bg-pink-50/50'"
                                @click="openSlotFilePicker(2)"
                                @dragenter.prevent="isDraggingSlot2 = true"
                                @dragover.prevent="isDraggingSlot2 = true"
                                @dragleave.prevent="if (!$el.contains($event.relatedTarget)) isDraggingSlot2 = false"
                                @drop.prevent="handleSlotDrop($event, 2)"
                                role="button"
                                tabindex="0"
                                @keydown.enter.prevent="openSlotFilePicker(2)"
                                @keydown.space.prevent="openSlotFilePicker(2)"
                                aria-label="Upload Photo 2 — drag and drop or press Enter to browse"
                            >
                                <template x-if="previewUrl2">
                                    <div class="relative">
                                        <img
                                            :src="previewUrl2"
                                            alt="Photo 2 preview"
                                            class="h-56 w-full rounded-lg object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <div class="pointer-events-none absolute inset-0 flex items-end justify-center rounded-lg pb-2">
                                            <span class="rounded-full bg-black/50 px-3 py-1 text-xs font-medium text-white">
                                                Click or drag to replace
                                            </span>
                                        </div>
                                        <button
                                            type="button"
                                            @click.stop="removeSlotFile(2)"
                                            class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 shadow hover:bg-red-50"
                                            aria-label="Remove Photo 2"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="!previewUrl2">
                                    <div class="flex h-56 flex-col items-center justify-center p-4 text-center pointer-events-none">
                                        <svg
                                            class="mb-3 h-10 w-10 transition-colors"
                                            :class="isDraggingSlot2 ? 'text-pink-400' : 'text-gray-300'"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                        </svg>
                                        <p
                                            class="text-sm font-medium transition-colors"
                                            :class="isDraggingSlot2 ? 'text-pink-600' : 'text-gray-600'"
                                            x-text="isDraggingSlot2 ? 'Drop photo here' : 'Drag & drop or click to browse'"
                                        ></p>
                                        <p class="mt-1 text-xs text-gray-400">JPG, PNG, WEBP · max 10 MB</p>
                                    </div>
                                </template>
                                <input
                                    x-ref="slotFileInput2"
                                    type="file"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    class="hidden"
                                    @change="handleSlotFileSelect($event, 2)"
                                >
                            </div>
                        @endif
                    </div>

                    <p class="text-sm text-gray-600">
                        Use the same note, crumple it slightly, and hold it in your other hand while keeping text readable.
                    </p>
                </div>
            </div>

            {{-- Inline upload button shown when both pending photos are ready --}}
            <template x-if="pendingPhoto1 && pendingPhoto2">
                <div class="mt-4 flex items-center justify-between gap-4 rounded-xl border border-pink-100 bg-pink-50 px-4 py-3">
                    <p class="text-sm font-medium text-pink-800">Both photos ready — click to submit for review.</p>
                    <button
                        type="button"
                        @click="uploadSlotPhotos()"
                        :disabled="isUploadingSlots"
                        class="shrink-0 rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span x-show="!isUploadingSlots" x-cloak>Upload &amp; submit</span>
                        <span x-show="isUploadingSlots" x-cloak>Uploading…</span>
                    </button>
                </div>
            </template>

            <p class="mt-6 text-sm text-gray-600">
                We do <span class="font-semibold">not</span> publish verification photos. If needed, you can also contact support:
                <a href="mailto:alice@hotescorts.com.au" class="font-medium text-pink-700 hover:text-pink-800">
                    alice@hotescorts.com.au
                </a>
            </p>

            <p class="mt-3 text-sm font-semibold text-pink-700">
                Profiles without verification can still be listed. Verification adds a "Photos Verified" badge.
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
                        loading="lazy"
                        decoding="async"
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
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 p-3 sm:p-4"
        @click.self="closeModal()"
        @keydown.escape.window="closeModal()"
    >
        <div class="flex min-h-full items-end justify-center sm:items-center">
            <div
                class="flex w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:max-h-[90vh]"
                role="dialog"
                aria-modal="true"
                aria-label="Photo verification upload modal"
            >
                <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-gray-900 sm:text-xl">Upload verification photos</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Add at least two clear photos holding your verification note.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeModal()"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gray-200 text-xl leading-none text-gray-500 transition hover:bg-gray-50 hover:text-gray-700"
                            aria-label="Close modal"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 rounded-xl bg-gray-100 p-1">
                        <button
                            type="button"
                            @click="switchTab('files')"
                            class="rounded-lg px-4 py-2.5 text-sm font-semibold transition sm:text-base"
                            :class="activeTab === 'files' ? 'bg-white text-pink-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        >
                            My Files
                        </button>

                        <button
                            type="button"
                            @click="switchTab('camera')"
                            class="rounded-lg px-4 py-2.5 text-sm font-semibold transition sm:text-base"
                            :class="activeTab === 'camera' ? 'bg-white text-pink-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Camera
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6">
                    <div x-show="activeTab === 'files'" x-cloak x-transition class="space-y-4">
                        <div
                            class="flex min-h-52 flex-col items-center justify-center rounded-xl border-2 border-dashed p-5 text-center transition sm:min-h-60 sm:p-8"
                            :class="isDragging ? 'border-pink-400 bg-pink-50' : 'border-gray-300 bg-white'"
                            @dragenter.prevent="isDragging = true"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop($event)"
                        >
                            <div class="mb-3 text-4xl sm:text-5xl">📁</div>
                            <p class="text-base font-semibold text-gray-700 sm:text-lg">Drag and drop photos here</p>
                            <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                                JPG, PNG, WEBP supported · min 2 photos · max 2 photos · max 10MB each
                            </p>

                            <button
                                type="button"
                                @click="openFilePicker()"
                                class="mt-5 inline-flex items-center rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-pink-700 sm:px-6 sm:text-base"
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
                            <div class="rounded-xl border border-gray-200 bg-white p-4">
                                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-semibold text-gray-700">
                                        Selected files (<span x-text="selectedFiles.length"></span>/2)
                                    </p>

                                    <button
                                        type="button"
                                        @click="clearSelectedFiles()"
                                        class="text-left text-xs font-semibold text-red-600 transition hover:text-red-700 sm:text-right"
                                    >
                                        Delete all
                                    </button>
                                </div>

                                <div class="max-h-64 space-y-2 overflow-y-auto pr-1">
                                    <template x-for="(file, index) in selectedFiles" :key="file.name + file.size + index">
                                        <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm text-gray-700" x-text="file.name"></p>
                                                <p class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></p>
                                            </div>

                                            <button
                                                type="button"
                                                @click="removeSelectedFile(index)"
                                                class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:bg-red-50"
                                                aria-label="Delete selected photo"
                                            >
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="activeTab === 'camera'" x-cloak x-transition class="space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-white p-4">
                            <video x-ref="video" autoplay playsinline muted class="aspect-video max-h-72 w-full rounded-lg bg-gray-200 object-contain"></video>
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
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <p class="mb-2 text-sm font-semibold text-gray-700">Captured preview</p>

                                    <div class="relative w-full max-w-md overflow-hidden rounded-lg border border-gray-200 bg-white p-2">
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

                                        <img :src="capturedImage" alt="Captured photo" class="max-h-72 w-full rounded-lg border-2 border-pink-300 object-contain" loading="lazy" decoding="async">
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
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-white px-4 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-600">
                            Ready to upload: <span class="font-semibold text-gray-900" x-text="selectedFiles.length"></span> / 2 photos
                        </p>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button
                                type="button"
                                @click="closeModal()"
                                class="w-full rounded-lg border border-gray-200 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 sm:w-auto"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                @click="uploadFiles()"
                                :disabled="isUploading || selectedFiles.length !== 2"
                                class="w-full rounded-lg bg-pink-600 px-6 py-3 font-semibold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                            >
                                <span x-show="!isUploading" x-cloak>Upload selected photos</span>
                                <span x-show="isUploading" x-cloak>Uploading...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('profile/js/verify-page.js') }}?v={{ filemtime(public_path('profile/js/verify-page.js')) }}"></script>
@endpush
