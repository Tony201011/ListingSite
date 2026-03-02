@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="{
    photos: [
        { id: 1, label: 'Photo 1' },
        { id: 2, label: 'Photo 2' },
        { id: 3, label: 'Photo 3' },
        { id: 4, label: 'Photo 4' },
        { id: 5, label: 'Photo 5' },
        { id: 6, label: 'Photo 6' }
    ],
    coverPhotoId: 1,
    setCover(id) {
        this.coverPhotoId = id;
    },
    removePhoto(id) {
        this.photos = this.photos.filter(photo => photo.id !== id);

        if (this.coverPhotoId === id) {
            this.coverPhotoId = this.photos.length ? this.photos[0].id : null;
        }
    }
}">
    <div class="max-w-5xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My photos</h1>
                <a href="{{ url('/add-photo') }}" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Add photos</a>
            </div>

            <div class="mb-5 rounded-lg border border-pink-100 bg-pink-50 px-4 py-3 text-sm text-pink-800" x-show="coverPhotoId" x-transition>
                Main photo: <span class="font-semibold" x-text="photos.find(photo => photo.id === coverPhotoId)?.label ?? 'Not selected'"></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" x-show="photos.length">
                <template x-for="photo in photos" :key="photo.id">
                    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white">
                        <div class="aspect-[3/4] bg-gray-100 flex items-center justify-center text-sm text-gray-500" x-text="photo.label"></div>
                        <div class="p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700" x-text="photo.label"></span>
                                <span x-show="coverPhotoId === photo.id" class="text-xs font-semibold text-pink-700 bg-pink-100 px-2 py-1 rounded-full">Cover photo</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="setCover(photo.id)"
                                    class="flex-1 px-3 py-2 text-xs font-semibold rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 transition"
                                >
                                    Set as cover
                                </button>
                                <button
                                    type="button"
                                    @click="removePhoto(photo.id)"
                                    class="px-3 py-2 text-xs font-semibold rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="!photos.length" class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                No photos available. Add a new photo to set your cover photo.
            </div>
        </div>
    </div>
</div>
@endsection
