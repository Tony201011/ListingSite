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

            @if(isset($latestVerification) && $latestVerification)
                <div class="mt-5 rounded-xl border px-4 py-3
                    {{ $latestVerification->status === 'approved' ? 'border-green-200 bg-green-50 text-green-800' : '' }}
                    {{ $latestVerification->status === 'pending' ? 'border-yellow-200 bg-yellow-50 text-yellow-800' : '' }}
                    {{ $latestVerification->status === 'rejected' ? 'border-red-200 bg-red-50 text-red-800' : '' }}
                ">
                    <p class="text-sm font-semibold">
                        Latest verification status:
                        <span class="capitalize">{{ $latestVerification->status }}</span>
                    </p>

                    @if($latestVerification->submitted_at)
                        <p class="text-xs mt-1">
                            Submitted on {{ $latestVerification->submitted_at->format('d M Y, h:i A') }}
                        </p>
                    @endif

                    @if($latestVerification->admin_note)
                        <p class="text-xs mt-2">
                            Note: {{ $latestVerification->admin_note }}
                        </p>
                    @endif
                </div>
            @endif

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
                <div
                    x-show="successMessage"
                    x-transition
                    class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p x-text="successMessage"></p>
                        <button type="button" @click="successMessage = ''" class="text-green-700 hover:text-green-900 font-bold">&times;</button>
                    </div>
                </div>

                <div
                    x-show="errorMessage"
                    x-transition
                    class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p class="whitespace-pre-line" x-text="errorMessage"></p>
                        <button type="button" @click="errorMessage = ''" class="text-red-700 hover:text-red-900 font-bold">&times;</button>
                    </div>
                </div>

                <div x-show="activeTab === 'files'" x-transition>
                    <div class="border-2 border-dashed rounded-xl p-8 sm:p-10 text-center transition" :class="isDragging ? 'border-pink-400 bg-pink-50' : 'border-gray-300 bg-white'" @dragenter.prevent="isDragging = true" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)">
                        <div class="text-5xl mb-4">📁</div>
                        <p class="text-lg font-semibold text-gray-700">Drag & drop photos here</p>
                        <p class="text-sm text-gray-500 mt-1 mb-5">JPG, PNG, WEBP supported</p>
                        <button type="button" @click="openFilePicker()" class="inline-flex items-center px-6 py-2.5 rounded-lg text-white font-medium bg-pink-600 hover:bg-pink-700 transition">Browse files</button>
                        <input x-ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="handleFileSelect($event)">
                    </div>

                    <template x-if="filePreviews.length > 0">
                        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-semibold text-gray-700">Selected files (<span x-text="filePreviews.length"></span>)</p>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="uploadFiles()"
                                        :disabled="uploading"
                                        class="text-xs font-semibold px-3 py-1.5 rounded-full bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
                                    >
                                        <span x-text="uploading ? 'Uploading...' : 'Upload'"></span>
                                    </button>
                                    <button type="button" @click="clearSelectedFiles()" class="text-xs font-semibold text-red-600 hover:text-red-700">Delete all</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-60 overflow-y-auto p-1">
                                <template x-for="(preview, index) in filePreviews" :key="preview">
                                    <div class="relative group aspect-square rounded-lg border border-gray-200 overflow-hidden bg-gray-100">
                                        <img :src="preview" :alt="'Preview ' + (index + 1)" class="w-full h-full object-cover">
                                        <button type="button" @click="removeSelectedFile(index)" class="absolute top-1 right-1 h-6 w-6 rounded-full bg-white/90 border border-red-200 text-red-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-50">
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
                            <button type="button" @click="uploadFiles()" :disabled="uploading || !selectedFiles.length" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition disabled:opacity-50">
                                <span x-text="uploading ? 'Uploading...' : 'Upload'"></span>
                            </button>
                        </div>

                        <template x-if="filePreviews.length > 0">
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Captured preview</p>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                    <template x-for="(preview, index) in filePreviews" :key="preview">
                                        <div class="relative inline-block">
                                            <button type="button" @click="removeSelectedFile(index)" class="absolute top-1.5 right-1.5 z-10 h-6 w-6 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <img :src="preview" alt="Captured photo" class="w-28 h-28 object-cover rounded-lg border-2 border-pink-300">
                                        </div>
                                    </template>
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
            filePreviews: [],
            stream: null,
            uploading: false,
            successMessage: '',
            errorMessage: '',

            openModal() {
                this.isModalOpen = true;
                this.clearMessages();
            },

            closeModal() {
                this.isModalOpen = false;
                this.stopCamera();
            },

            clearMessages() {
                this.successMessage = '';
                this.errorMessage = '';
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

            isFileDuplicate(newFile) {
                return this.selectedFiles.some(existingFile =>
                    existingFile.name === newFile.name &&
                    existingFile.size === newFile.size &&
                    existingFile.lastModified === newFile.lastModified
                );
            },

            handleFileSelect(event) {
                this.clearMessages();

                const files = Array.from(event.target.files || []);
                const uniqueFiles = files.filter(file => !this.isFileDuplicate(file));

                uniqueFiles.forEach(file => {
                    this.selectedFiles.push(file);
                    this.filePreviews.push(URL.createObjectURL(file));
                });

                this.$refs.fileInput.value = '';
            },

            handleDrop(event) {
                this.clearMessages();
                this.isDragging = false;

                const files = Array.from(event.dataTransfer.files || []);
                const uniqueFiles = files.filter(file => !this.isFileDuplicate(file));

                uniqueFiles.forEach(file => {
                    this.selectedFiles.push(file);
                    this.filePreviews.push(URL.createObjectURL(file));
                });
            },

            removeSelectedFile(index) {
                URL.revokeObjectURL(this.filePreviews[index]);
                this.filePreviews.splice(index, 1);
                this.selectedFiles.splice(index, 1);

                if (!this.selectedFiles.length && this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            clearSelectedFiles() {
                this.filePreviews.forEach(url => URL.revokeObjectURL(url));
                this.filePreviews = [];
                this.selectedFiles = [];

                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            async startCamera() {
                this.clearMessages();

                if (this.stream) return;

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    this.$refs.video.srcObject = this.stream;
                } catch (error) {
                    this.errorMessage = 'Camera access denied or not available.';
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
                this.clearMessages();

                const video = this.$refs.video;
                const canvas = this.$refs.canvas;

                if (!video || !canvas || !video.videoWidth) {
                    this.errorMessage = 'Camera is not ready yet.';
                    return;
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                const dataURL = canvas.toDataURL('image/png');
                const file = this.dataURLtoFile(dataURL, `verification_${Date.now()}.png`);

                this.selectedFiles.push(file);
                this.filePreviews.push(URL.createObjectURL(file));
            },

            dataURLtoFile(dataurl, filename) {
                const arr = dataurl.split(',');
                const mime = arr[0].match(/:(.*?);/)[1];
                const bstr = atob(arr[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);

                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }

                return new File([u8arr], filename, { type: mime });
            },

            async uploadFiles() {
                this.clearMessages();

                if (!this.selectedFiles.length) {
                    this.errorMessage = 'Please select at least one verification photo.';
                    return;
                }

                if (this.uploading) return;

                this.uploading = true;

                const formData = new FormData();

                this.selectedFiles.forEach((file, index) => {
                    formData.append(`photos[${index}]`, file);
                });

                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route('verify.photos.upload') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        if (result.errors) {
                            const allErrors = Object.values(result.errors).flat().join('\n');
                            throw new Error(allErrors);
                        }

                        throw new Error(result.message || 'Upload failed.');
                    }

                    this.successMessage = result.message || 'Verification photos uploaded successfully.';
                    this.clearSelectedFiles();

                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);

                } catch (error) {
                    this.errorMessage = error.message || 'Something went wrong.';
                } finally {
                    this.uploading = false;
                }
            }
        }
    }
</script>
@endsection
