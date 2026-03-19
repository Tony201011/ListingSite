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
                    <a href="{{ url('/after-image-upload') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 rounded-full font-semibold border border-pink-300 text-pink-700 hover:bg-pink-50 transition">
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

                    <!-- Thumbnail grid for selected files (including captured photos) -->
                    <template x-if="filePreviews.length > 0">
                        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-semibold text-gray-700">Selected (<span x-text="filePreviews.length"></span>)</p>
                                <div class="flex items-center gap-2">
                                    <!-- Upload button -->
                                    <button
                                        type="button"
                                        @click="uploadFiles()"
                                        :disabled="uploading"
                                        class="text-xs font-semibold px-3 py-1.5 rounded-full bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                                    >
                                        <svg x-show="!uploading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        <svg x-show="uploading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-60 overflow-y-auto p-1">
                                <template x-for="(preview, index) in filePreviews" :key="preview">
                                    <div class="relative group aspect-square rounded-lg border border-gray-200 overflow-hidden bg-gray-100 cursor-pointer" @click="openSlider(index)">
                                        <img :src="preview" :alt="'Preview ' + (index+1)" class="w-full h-full object-cover">
                                        <button
                                            type="button"
                                            @click.stop="removeSelectedFile(index)"
                                            class="absolute top-1 right-1 h-6 w-6 rounded-full bg-white/90 border border-red-200 text-red-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-50"
                                            aria-label="Delete photo"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Slider/Lightbox Modal -->
    <div x-show="sliderOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4" @keydown.escape="closeSlider()" @keydown.left="prevSlide()" @keydown.right="nextSlide()" tabindex="0" x-trap.noscroll="sliderOpen">
        <button type="button" @click="closeSlider()" class="absolute top-4 right-4 text-white/80 hover:text-white text-4xl leading-none z-10">&times;</button>

        <button type="button" @click="prevSlide()" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white/80 hover:text-white text-5xl leading-none z-10" :class="{ 'opacity-50 cursor-not-allowed': filePreviews.length <= 1 }">&lsaquo;</button>
        <button type="button" @click="nextSlide()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/80 hover:text-white text-5xl leading-none z-10" :class="{ 'opacity-50 cursor-not-allowed': filePreviews.length <= 1 }">&rsaquo;</button>

        <template x-if="filePreviews.length > 0">
            <img :src="filePreviews[sliderIndex]" class="max-h-full max-w-full object-contain rounded-lg" :alt="'Slide ' + (sliderIndex + 1)">
        </template>
    </div>
</div>

<script>
    function addPhotoPage() {
        return {
            isModalOpen: false,
            activeTab: 'files',
            isDragging: false,
            selectedFiles: [],      // array of File objects (uploaded + captured)
            filePreviews: [],       // array of object URLs corresponding to selectedFiles
            stream: null,
            uploading: false,       // upload in progress
            sliderOpen: false,      // slider visibility
            sliderIndex: 0,         // current slide index

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

            // Check if a file already exists in selectedFiles (by name, size, lastModified)
            isFileDuplicate(newFile, existingFiles) {
                return existingFiles.some(existingFile =>
                    existingFile.name === newFile.name &&
                    existingFile.size === newFile.size &&
                    existingFile.lastModified === newFile.lastModified
                );
            },

            // Handle file selection via browse button (append only unique)
            handleFileSelect(event) {
                const newFiles = Array.from(event.target.files || []);
                // Filter out duplicates
                const uniqueNewFiles = newFiles.filter(file =>
                    !this.isFileDuplicate(file, this.selectedFiles)
                );

                // Append unique files
                this.selectedFiles.push(...uniqueNewFiles);
                uniqueNewFiles.forEach(file => {
                    this.filePreviews.push(URL.createObjectURL(file));
                });

                // Clear input so the same file can be selected again later (if removed)
                this.$refs.fileInput.value = '';
            },

            // Handle dropped files (append only unique)
            handleDrop(event) {
                this.isDragging = false;
                const newFiles = Array.from(event.dataTransfer.files || []);
                // Filter out duplicates
                const uniqueNewFiles = newFiles.filter(file =>
                    !this.isFileDuplicate(file, this.selectedFiles)
                );

                this.selectedFiles.push(...uniqueNewFiles);
                uniqueNewFiles.forEach(file => {
                    this.filePreviews.push(URL.createObjectURL(file));
                });
            },

            // Remove a specific file by index
            removeSelectedFile(index) {
                // Revoke the object URL to free memory
                URL.revokeObjectURL(this.filePreviews[index]);
                this.filePreviews.splice(index, 1);
                this.selectedFiles.splice(index, 1);

                // If no files left, clear the input (optional)
                if (!this.selectedFiles.length && this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            // Remove all files and revoke all URLs
            clearSelectedFiles() {
                this.filePreviews.forEach(url => URL.revokeObjectURL(url));
                this.filePreviews = [];
                this.selectedFiles = [];

                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            // Camera methods
            async startCamera() {
                if (this.stream) return;
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

            // Capture a photo from the camera and add it to the list
            capturePhoto() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                if (!video || !canvas || !video.videoWidth) return;

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                const dataURL = canvas.toDataURL('image/png');
                // Convert data URL to a File object (always unique due to timestamp)
                const file = this.dataURLtoFile(dataURL, `capture_${Date.now()}.png`);

                // Append to the existing lists (no duplicate check needed because filename includes timestamp)
                this.selectedFiles.push(file);
                this.filePreviews.push(URL.createObjectURL(file));
            },

            // Helper: convert data URL to File object
            dataURLtoFile(dataurl, filename) {
                let arr = dataurl.split(',');
                let mime = arr[0].match(/:(.*?);/)[1];
                let bstr = atob(arr[1]);
                let n = bstr.length;
                let u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], filename, { type: mime });
            },

            // Upload all selected files to the server
            async uploadFiles() {
                if (this.selectedFiles.length === 0 || this.uploading) return;

                this.uploading = true;

                // Prepare FormData
                const formData = new FormData();
                this.selectedFiles.forEach((file, index) => {
                    formData.append(`photos[${index}]`, file);
                });

                // Add CSRF token for Laravel
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('/upload-photos', { // Change this URL to your endpoint
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest' // Optional, but good for Laravel
                        }
                    });

                    const result = await response.json();

                    if (response.ok) {
                        // Success – you can choose to clear the selection or keep it
                        // this.clearSelectedFiles(); // Uncomment to clear after upload
                        alert('Upload successful!');
                        // Optionally close the modal
                        // this.closeModal();
                    } else {
                        alert('Upload failed: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    alert('Network error: ' + error.message);
                } finally {
                    this.uploading = false;
                }
            },

            // Slider methods
            openSlider(index) {
                this.sliderIndex = index;
                this.sliderOpen = true;
            },

            closeSlider() {
                this.sliderOpen = false;
            },

            nextSlide() {
                if (this.filePreviews.length > 1) {
                    this.sliderIndex = (this.sliderIndex + 1) % this.filePreviews.length;
                }
            },

            prevSlide() {
                if (this.filePreviews.length > 1) {
                    this.sliderIndex = (this.sliderIndex - 1 + this.filePreviews.length) % this.filePreviews.length;
                }
            }
        };
    }
</script>
@endsection
