@extends('layouts.frontend')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="verifyPage({
        uploadUrl: @js(route('verify.photos.upload')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="max-w-5xl mx-auto space-y-6">

        <a href="{{ url('/view-profile-setting') }}"
           class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>

        <!-- KEEP YOUR FULL EXISTING HTML SAME (no change needed above modal) -->

        <!-- Modal -->
        <div
            x-show="isModalOpen"
            x-cloak
            class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
            @click.self="closeModal()"
        >
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">

                <!-- Tabs -->
                <div class="flex items-center border-b px-4 pt-4">
                    <button @click="switchTab('files')" class="flex-1 pb-3 font-semibold"
                            :class="activeTab === 'files' ? 'text-pink-600 border-b-2 border-pink-600' : ''">
                        My Files
                    </button>

                    <button @click="switchTab('camera')" class="flex-1 pb-3 font-semibold"
                            :class="activeTab === 'camera' ? 'text-pink-600 border-b-2 border-pink-600' : ''">
                        Camera
                    </button>

                    <button @click="closeModal()" class="ml-4 text-2xl">&times;</button>
                </div>

                <!-- Content -->
                <div class="p-4 bg-gray-50">

                    <!-- Upload -->
                    <div x-show="activeTab === 'files'">

                        <input x-ref="fileInput" type="file" multiple class="hidden"
                               @change="handleFileSelect($event)">

                        <button @click="$refs.fileInput.click()" class="bg-pink-600 text-white px-4 py-2">
                            Select files
                        </button>

                        <template x-if="filePreviews.length">
                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <template x-for="(img, i) in filePreviews" :key="i">
                                    <img :src="img" class="h-24 object-cover">
                                </template>
                            </div>
                        </template>

                        <button @click="uploadFiles()" class="mt-4 bg-green-600 text-white px-4 py-2">
                            Upload
                        </button>
                    </div>

                    <!-- Camera -->
                    <div x-show="activeTab === 'camera'">
                        <video x-ref="video" autoplay class="w-full"></video>
                        <canvas x-ref="canvas" class="hidden"></canvas>

                        <button @click="startCamera()">Start</button>
                        <button @click="capturePhoto()">Capture</button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('profile/js/verify-profie-photo.js') }}"></script>
@endpush
