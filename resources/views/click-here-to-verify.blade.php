@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="verifyPage()">
    <div class="max-w-5xl mx-auto space-y-6">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Verify your profile photos</h1>
            <p class="mt-3 text-gray-600">Photo verification is optional. Complete it to get a “Photos Verified” badge on your profile.</p>

            <div class="mt-6 rounded-xl border border-pink-100 bg-pink-50 p-4">
                <p class="text-sm font-semibold text-pink-800">Verification note format</p>
                <p class="mt-1 text-pink-700 font-medium">your profile name * "Find me on Hotescorts.com.au" + today’s date</p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-sm font-semibold text-gray-900 mb-2">Photo 1</p>
                    <p class="text-sm text-gray-600">Hold the note clearly in one hand. Your face or matching profile features must be visible.</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-sm font-semibold text-gray-900 mb-2">Photo 2</p>
                    <p class="text-sm text-gray-600">Use the same note, crumple it slightly, and hold it in your other hand while keeping text readable.</p>
                </div>
            </div>

            <p class="mt-6 text-sm text-gray-600">
                We do <span class="font-semibold">not</span> publish verification photos. If needed, you can also contact support:
                <a href="mailto:alice@hotescorts.com.au" class="text-pink-700 hover:text-pink-800 font-medium">alice@hotescorts.com.au</a>
            </p>

            <p class="mt-3 text-sm font-semibold text-pink-700">Profiles without verification can still be listed. Verification adds a “Photos Verified” badge.</p>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button type="button" @click="openModal()" class="px-6 py-3 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">
                    Upload photos for verified badge
                </button>
                <button type="button" onclick="window.history.back()" class="px-6 py-3 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold transition">
                    Back
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900">Example format</h2>
            <p class="mt-2 text-sm text-gray-600">Write exactly: <span class="text-pink-700 font-semibold">your profile name * "Find me on Hotescorts.com.au" + today’s date</span></p>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                    <img src="https://dummyimage.com/900x600/f3f4f6/6b7280&text=Example+1+-+Clear+note+%2B+face" alt="Verification example 1" class="w-full h-44 object-cover">
                    <p class="px-3 py-2 text-xs text-gray-600">Example 1: clear note + visible face</p>
                </div>
                <div class="rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                    <img src="https://dummyimage.com/900x600/f3f4f6/6b7280&text=Example+2+-+Same+note%2C+other+hand" alt="Verification example 2" class="w-full h-44 object-cover">
                    <p class="px-3 py-2 text-xs text-gray-600">Example 2: same note in other hand</p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="isModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="closeModal()">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center border-b border-gray-200 px-4 sm:px-6 pt-4">
                <button type="button" @click="switchTab('files')" class="flex-1 pb-3 text-sm sm:text-base font-semibold border-b-2 transition" :class="activeTab === 'files' ? 'text-pink-600 border-pink-600' : 'text-gray-500 border-transparent'">
                    My Files
                </button>
                <button type="button" @click="switchTab('camera')" class="flex-1 pb-3 text-sm sm:text-base font-semibold border-b-2 transition" :class="activeTab === 'camera' ? 'text-pink-600 border-pink-600' : 'text-gray-500 border-transparent'">
                    Camera
                </button>
                <button type="button" @click="closeModal()" class="ml-4 text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>

            <div class="p-4 sm:p-6 bg-gray-50">
                <div x-show="activeTab === 'files'" x-transition>
                    <div class="border-2 border-dashed rounded-xl p-8 sm:p-10 text-center transition" :class="isDragging ? 'border-pink-400 bg-pink-50' : 'border-gray-300 bg-white'" @dragenter.prevent="isDragging = true" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)">
                        <div class="text-5xl mb-4">📁</div>
                        <p class="text-lg font-semibold text-gray-700">Drag & drop photos here</p>
                        <p class="text-sm text-gray-500 mt-1 mb-5">JPG, PNG, WEBP supported</p>
                        <button type="button" @click="openFilePicker()" class="inline-flex items-center px-6 py-2.5 rounded-lg text-white font-medium bg-pink-600 hover:bg-pink-700 transition">Browse files</button>
                        <input x-ref="fileInput" type="file" multiple class="hidden" @change="handleFileSelect($event)">
                    </div>

                    <template x-if="selectedFiles.length > 0">
                        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-700">Selected files (<span x-text="selectedFiles.length"></span>)</p>
                                <button type="button" @click="clearSelectedFiles()" class="text-xs font-semibold text-red-600 hover:text-red-700">Delete all</button>
                            </div>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <template x-for="(file, index) in selectedFiles" :key="file.name + file.size + index">
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2">
                                        <p class="text-sm text-gray-600 truncate" x-text="file.name"></p>
                                        <button type="button" @click="removeSelectedFile(index)" class="h-6 w-6 shrink-0 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition" aria-label="Delete selected photo">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="activeTab === 'camera'" x-transition>
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <video x-ref="video" autoplay playsinline class="w-full max-h-72 rounded-lg bg-gray-200"></video>
                        <canvas x-ref="canvas" class="hidden"></canvas>
                        <div class="mt-4 flex flex-col sm:flex-row gap-3">
                            <button type="button" @click="startCamera()" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-pink-100 text-pink-700 font-medium hover:bg-pink-200 transition">Start camera</button>
                            <button type="button" @click="capturePhoto()" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-pink-600 text-white font-medium hover:bg-pink-700 transition">Capture</button>
                        </div>

                        <template x-if="capturedImage">
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Captured preview</p>
                                <div class="relative inline-block">
                                    <button type="button" @click="clearCapturedPhoto()" class="absolute top-1.5 right-1.5 z-10 h-6 w-6 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition" aria-label="Delete captured photo">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <img :src="capturedImage" alt="Captured photo" class="w-28 h-28 object-cover rounded-lg border-2 border-pink-300">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function verifyPage() {
        return {
            isModalOpen: false,
            activeTab: 'files',
            isDragging: false,
            selectedFiles: [],
            capturedImage: '',
            stream: null,

            openModal() {
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
                this.stopCamera();
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
                this.$refs.fileInput.click();
            },

            handleFileSelect(event) {
                this.selectedFiles = Array.from(event.target.files || []);
            },

            handleDrop(event) {
                this.isDragging = false;
                this.selectedFiles = Array.from(event.dataTransfer.files || []);
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

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    this.$refs.video.srcObject = this.stream;
                } catch (error) {
                    alert('Camera access denied or not available.');
                }
            },

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
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

            clearCapturedPhoto() {
                this.capturedImage = '';
            }
        }
    }
</script>

@endsection
