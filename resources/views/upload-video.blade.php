@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{
    videoName: '',
    previewUrl: '',
    handleVideoChange(event) {
        const file = event.target.files?.[0];

        if (!file) {
            this.videoName = '';
            this.previewUrl = '';
            return;
        }

        this.videoName = file.name;
        this.previewUrl = URL.createObjectURL(file);
    },
    clearSelection() {
        this.videoName = '';
        this.previewUrl = '';
        this.$refs.videoInput.value = '';
    }
}">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/my-videos') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to my videos
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Upload video</h1>
            <p class="text-gray-600 mb-6">Add a short clip for your profile. MP4/MOV up to 100MB.</p>

            <div class="rounded-xl border-2 border-dashed border-pink-200 bg-pink-50/50 p-6 text-center">
                <input
                    x-ref="videoInput"
                    type="file"
                    accept="video/mp4,video/quicktime,video/*"
                    class="hidden"
                    @change="handleVideoChange($event)"
                >

                <template x-if="!videoName">
                    <div>
                        <p class="text-sm text-gray-600 mb-4">Drag & drop is optional. Click to choose your video.</p>
                        <button type="button" @click="$refs.videoInput.click()" class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">
                            Choose video file
                        </button>
                    </div>
                </template>

                <template x-if="videoName">
                    <div class="space-y-4">
                        <video x-show="previewUrl" :src="previewUrl" controls class="w-full rounded-lg border border-gray-200"></video>
                        <p class="text-sm font-medium text-gray-700" x-text="videoName"></p>
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" @click="$refs.videoInput.click()" class="px-4 py-2 rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 text-sm font-semibold transition">Replace</button>
                            <button type="button" @click="clearSelection()" class="px-4 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold transition">Delete</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex items-center gap-2">
                <button type="button" class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Upload now</button>
                <a href="{{ url('/my-videos') }}" class="px-5 py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold transition">Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection
