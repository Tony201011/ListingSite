@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="uploadVideoPage()">
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('my-videos') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to my videos
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Upload videos</h1>
            <p class="text-gray-600 mb-6">Add short clips for your profile. MP4/MOV up to 100MB each.</p>

            <!-- Success message -->
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

            <!-- Error message -->
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
                        <p class="text-sm text-gray-600 mb-4">
                            Drag & drop multiple videos here or click to choose files.
                        </p>
                        <button
                            type="button"
                            @click="$refs.videoInput.click()"
                            class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition"
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
                                    class="text-xs font-semibold px-3 py-1.5 rounded-full bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[28rem] overflow-y-auto p-1">
                            <template x-for="(video, index) in selectedVideos" :key="video.key">
                                <div class="relative rounded-xl border border-gray-200 bg-white overflow-hidden">
                                    <button
                                        type="button"
                                        @click="removeSelectedVideo(index)"
                                        class="absolute top-1.5 right-1.5 z-10 h-7 w-7 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition"
                                        aria-label="Delete selected video"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <video :src="video.previewUrl" controls class="w-full aspect-video object-cover bg-black"></video>

                                    <div class="p-3 text-left">
                                        <p class="text-sm font-medium text-gray-700 break-all" x-text="video.name"></p>
                                        <p class="text-xs text-gray-500 mt-1" x-text="formatFileSize(video.size)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-center gap-2">
                            <button
                                type="button"
                                @click="$refs.videoInput.click()"
                                class="px-4 py-2 rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 text-sm font-semibold transition"
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
                    class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition disabled:opacity-50"
                >
                    <span x-text="uploading ? 'Uploading...' : 'Upload now'"></span>
                </button>

                <a href="{{ route('my-videos') }}" class="px-5 py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold transition">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('uploadVideoPage', () => ({
        selectedVideos: [],
        uploading: false,
        successMessage: '',
        errorMessage: '',
        isDragging: false,

        clearMessages() {
            this.successMessage = '';
            this.errorMessage = '';
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            return (bytes / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
        },

        isDuplicate(file) {
            return this.selectedVideos.some(video =>
                video.name === file.name &&
                video.size === file.size &&
                video.lastModified === file.lastModified
            );
        },

        addFiles(files) {
            const incomingFiles = Array.from(files || []);

            incomingFiles.forEach(file => {
                if (this.isDuplicate(file)) return;

                this.selectedVideos.push({
                    key: `${file.name}-${file.size}-${file.lastModified}-${Math.random()}`,
                    file: file,
                    name: file.name,
                    size: file.size,
                    lastModified: file.lastModified,
                    previewUrl: URL.createObjectURL(file),
                });
            });

            if (this.$refs.videoInput) {
                this.$refs.videoInput.value = '';
            }
        },

        handleVideoChange(event) {
            this.clearMessages();
            this.addFiles(event.target.files);
        },

        handleDrop(event) {
            this.clearMessages();
            this.isDragging = false;
            this.addFiles(event.dataTransfer.files);
        },

        removeSelectedVideo(index) {
            if (this.selectedVideos[index]?.previewUrl) {
                URL.revokeObjectURL(this.selectedVideos[index].previewUrl);
            }

            this.selectedVideos.splice(index, 1);

            if (!this.selectedVideos.length && this.$refs.videoInput) {
                this.$refs.videoInput.value = '';
            }
        },

        clearSelection() {
            this.selectedVideos.forEach(video => {
                if (video.previewUrl) {
                    URL.revokeObjectURL(video.previewUrl);
                }
            });

            this.selectedVideos = [];

            if (this.$refs.videoInput) {
                this.$refs.videoInput.value = '';
            }
        },

        async uploadVideos() {
            this.clearMessages();

            if (!this.selectedVideos.length) {
                this.errorMessage = 'Please select at least one video.';
                return;
            }

            if (this.uploading) return;

            this.uploading = true;

            const formData = new FormData();

            this.selectedVideos.forEach((video, index) => {
                formData.append(`videos[${index}]`, video.file);
            });

            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('videos.upload') }}', {
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

                this.successMessage = result.message || 'Videos uploaded successfully.';
                this.clearSelection();

                setTimeout(() => {
                    window.location.href = '{{ route('my-videos') }}';
                }, 1200);

            } catch (error) {
                this.errorMessage = error.message || 'Something went wrong.';
            } finally {
                this.uploading = false;
            }
        }
    }));
});
</script>
@endsection
