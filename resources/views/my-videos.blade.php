@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{
    videos: [
        { id: 1, label: 'Video slot 1' },
        { id: 2, label: 'Video slot 2' }
    ],
    removeVideo(id) {
        this.videos = this.videos.filter(video => video.id !== id);
    }
}">
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My videos</h1>
            <p class="text-gray-600 mb-6">Upload short preview clips to improve engagement and profile rank.</p>
            <a href="{{ route('upload-video') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Upload video</a>

            <div class="mt-6 grid sm:grid-cols-2 gap-4" x-show="videos.length">
                <template x-for="video in videos" :key="video.id">
                    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white">
                        <div class="aspect-video bg-gray-100 flex items-center justify-center text-gray-500" x-text="video.label"></div>
                        <div class="p-3 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700" x-text="video.label"></span>
                            <button
                                type="button"
                                @click="removeVideo(video.id)"
                                class="px-3 py-2 text-xs font-semibold rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="!videos.length" class="mt-6 rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                No videos available.
            </div>
        </div>
    </div>
</div>
@endsection
