@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" x-data="addPhotoPage()">
    <div class="max-w-4xl mx-auto">
        <button onclick="window.history.back()" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer">
            <span class="mr-1">&lt;</span> back to profile
        </button>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4 tracking-tight">Add photos to your profile</h1>

                <p class="max-w-2xl text-base sm:text-lg text-gray-600 leading-relaxed mb-8">
                    Upload from your device, drag and drop multiple files, or take a photo directly with your camera.
                    Keep your gallery fresh to improve profile quality and visibility.
                </p>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
                    <button type="button" @click="openModal()" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 rounded-full text-white font-semibold bg-pink-600 hover:bg-pink-700 transition shadow-lg shadow-pink-600/20">
                        Click to add photos
                    </button>
                    <a href="#" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 rounded-full font-semibold border border-pink-300 text-pink-700 hover:bg-pink-50 transition">
                        Continue setting up your profile
                    </a>
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
                        <p class="text-lg font-semibold text-gray-700">Drag & drop files here</p>
                        <p class="text-sm text-gray-500 mt-1 mb-5">JPG, PNG, WEBP supported</p>
                        <button type="button" @click="openFilePicker()" class="inline-flex items-center px-6 py-2.5 rounded-lg text-white font-medium bg-pink-600 hover:bg-pink-700 transition">Browse files</button>
                        <input x-ref="fileInput" type="file" multiple class="hidden" @change="handleFileSelect($event)">
                    </div>

                    <template x-if="selectedFiles.length > 0">
                        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Selected files (<span x-text="selectedFiles.length"></span>)</p>
                            <div class="space-y-1 max-h-32 overflow-y-auto">
                                <template x-for="file in selectedFiles" :key="file.name + file.size">
                                    <p class="text-sm text-gray-600 truncate" x-text="file.name"></p>
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
                                <img :src="capturedImage" alt="Captured photo" class="w-28 h-28 object-cover rounded-lg border-2 border-pink-300">
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function addPhotoPage() {
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
            }
        }
    }
</script>
@endsection
