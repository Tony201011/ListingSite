@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="videoGallery({
    videos: @js($videos->map(fn ($video) => [
        'id' => $video->id,
        'video_path' => $video->video_path,
        'video_url' => $video->video_url,
        'original_name' => $video->original_name,
    ])->values()),
    deleteUrl: @js(url('/videos/__ID__')),
    csrfToken: @js(csrf_token())
})">
    <div class="max-w-4xl mx-auto">
        @include('profile.partials.back-to-settings')

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My videos</h1>
            <p class="text-gray-600 mb-6">Upload short preview clips to improve engagement and profile rank.</p>

            <a href="{{ route('upload-video') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">
                Upload video
            </a>

            <!-- Success message -->
            <div
                x-show="successMessage"
                x-transition
                class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
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
                class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                <div class="flex items-start justify-between gap-3">
                    <p class="whitespace-pre-line" x-text="errorMessage"></p>
                    <button type="button" @click="errorMessage = ''" class="text-red-700 hover:text-red-900 font-bold">&times;</button>
                </div>
            </div>

            <div class="mt-6 grid sm:grid-cols-2 gap-4" x-show="videos.length">
                <template x-for="video in videos" :key="video.id">
                    <div class="relative rounded-xl border border-gray-200 overflow-hidden bg-white">
                        <button
                            type="button"
                            @click="askRemove(video.id)"
                            class="absolute top-1.5 right-1.5 z-10 h-6 w-6 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition"
                            :disabled="loading"
                            aria-label="Delete video"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div class="aspect-video bg-black">
                            <video :src="video.video_url" controls class="w-full h-full object-cover"></video>
                        </div>

                        <div class="p-3 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700" x-text="video.original_name || ('Video #' + video.id)"></span>
                        </div>

                        <!-- Delete confirm box -->
                        <div
                            x-show="confirmDeleteId === video.id"
                            x-transition
                            class="mx-3 mb-3 rounded-lg border border-red-200 bg-red-50 p-3"
                        >
                            <p class="text-xs text-red-700 mb-2">Are you sure you want to delete this video?</p>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="removeVideo(video.id)"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700"
                                    :disabled="loading"
                                >
                                    Yes, delete
                                </button>

                                <button
                                    type="button"
                                    @click="confirmDeleteId = null"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                                    :disabled="loading"
                                >
                                    Cancel
                                </button>
                            </div>
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

@push('scripts')
<script src="{{ asset('profile/js/video-gallery.js') }}?v={{ filemtime(public_path('profile/js/video-gallery.js')) }}"></script>
@endpush
@endsection
