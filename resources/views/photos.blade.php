@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="photoGallery()"
>
    <div class="max-w-5xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My photos</h1>
                <a href="{{ url('/add-photo') }}" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                    Add photos
                </a>
            </div>

            <div
                class="mb-5 rounded-lg border border-pink-100 bg-pink-50 px-4 py-3 text-sm text-pink-800"
                x-show="coverPhoto"
                x-transition
            >
                Main photo:
                <span class="font-semibold" x-text="coverPhoto ? ('Photo #' + coverPhoto.id) : 'Not selected'"></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" x-show="photos.length">
                <template x-for="photo in photos" :key="photo.id">
                    <div class="relative rounded-xl border border-gray-200 overflow-hidden bg-white">
                        <button
                            type="button"
                            @click="removePhoto(photo.id)"
                            class="absolute top-1.5 right-1.5 z-10 h-8 w-8 inline-flex items-center justify-center rounded-full bg-white/95 border border-red-200 text-red-600 hover:bg-red-50 transition"
                            aria-label="Delete photo"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div class="aspect-[3/4] bg-gray-100 overflow-hidden">
                            <img
                                :src="photo.thumbnail_url"
                                :alt="'Photo ' + photo.id"
                                class="w-full h-full object-cover"
                            >
                        </div>

                        <div class="p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700" x-text="'Photo #' + photo.id"></span>

                                <span
                                    x-show="photo.is_primary"
                                    class="text-xs font-semibold text-pink-700 bg-pink-100 px-2 py-1 rounded-full"
                                >
                                    Cover photo
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="setCover(photo.id)"
                                    class="w-full px-3 py-2 text-xs font-semibold rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 transition disabled:opacity-50"
                                    :disabled="photo.is_primary || loading"
                                >
                                    Set as cover
                                </button>

                                <a
                                    :href="photo.image_url"
                                    target="_blank"
                                    class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition"
                                >
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div
                x-show="!photos.length"
                class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500"
            >
                No photos available. Add a new photo to set your cover photo.
            </div>
        </div>
    </div>
</div>

<script>
    function photoGallery() {
        return {
            loading: false,
            photos: @json(
                $photos->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'image_path' => $photo->image_path,
                        'thumbnail_path' => $photo->thumbnail_path,
                        'image_url' => \Illuminate\Support\Facades\Storage::disk('s3')->url($photo->image_path),
                        'thumbnail_url' => \Illuminate\Support\Facades\Storage::disk('s3')->url($photo->thumbnail_path),
                        'is_primary' => (bool) $photo->is_primary,
                    ];
                })
            ),

            get coverPhoto() {
                return this.photos.find(photo => photo.is_primary) || null;
            },

            async setCover(id) {
                if (this.loading) return;

                this.loading = true;

                try {
                    const response = await fetch(`/photos/${id}/set-cover`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({})
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Failed to set cover photo.');
                    }

                    this.photos = this.photos.map(photo => ({
                        ...photo,
                        is_primary: photo.id === id
                    }));

                } catch (error) {
                    alert(error.message || 'Something went wrong.');
                } finally {
                    this.loading = false;
                }
            },

            async removePhoto(id) {
                if (this.loading) return;

                if (!confirm('Are you sure you want to delete this photo?')) {
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(`/photos/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Failed to delete photo.');
                    }

                    this.photos = this.photos.filter(photo => photo.id !== id);

                    if (!this.photos.some(photo => photo.is_primary) && this.photos.length > 0) {
                        this.photos[0].is_primary = true;
                    }

                } catch (error) {
                    alert(error.message || 'Something went wrong.');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endsection
