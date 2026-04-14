@extends('layouts.frontend')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<style>
    .ql-editor {
        min-height: 250px;
        font-size: 1rem !important;
        line-height: 1.6 !important;
        color: #1f2937 !important;
        background-color: #ffffff !important;
        font-family: inherit !important;
    }

    .ql-editor.ql-blank::before {
        color: #9ca3af !important;
        font-style: normal !important;
    }

    .ql-editor h1 {
        font-size: 2em !important;
        font-weight: 700 !important;
        margin-bottom: 0.5em !important;
    }

    .ql-editor h2 {
        font-size: 1.5em !important;
        font-weight: 600 !important;
        margin-bottom: 0.5em !important;
    }

    .ql-editor h3 {
        font-size: 1.25em !important;
        font-weight: 600 !important;
        margin-bottom: 0.5em !important;
    }

    .ql-editor a {
        color: #e04ecb !important;
        text-decoration: underline !important;
    }

    .ql-editor a:hover {
        color: #c13ab0 !important;
    }

    .ql-editor ul,
    .ql-editor ol {
        padding-left: 2em !important;
        margin-bottom: 1em !important;
    }

    .ql-editor blockquote {
        border-left: 4px solid #e04ecb !important;
        padding-left: 1em !important;
        margin-left: 0 !important;
        font-style: italic !important;
        color: #4b5563 !important;
    }

    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        border-color: #d1d5db !important;
    }

    .ql-container.ql-snow {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        border-color: #d1d5db !important;
    }
</style>
@endpush

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="profileMessageEditor({
        initialContent: @js($profileMessage ?? ''),
        storeUrl: @js(route('profile-message.store')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="mx-auto max-w-4xl">
        @include('profile.partials.back-to-settings')

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="mb-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                Profile message
            </h1>

            <p class="mb-6 text-gray-600">
                Set a short announcement for promotions, links, or important updates.
            </p>

            <div x-ref="editor" class="prose max-w-none"></div>

            <input type="hidden" x-model="content">

            <template x-if="errors.message">
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <span x-text="errors.message[0]"></span>
                </div>
            </template>

            <div class="mt-6 flex gap-3">
                <button
                    type="button"
                    @click="saveMessage()"
                    :disabled="loading"
                    class="rounded-lg bg-pink-600 px-5 py-2.5 font-semibold text-white transition hover:bg-pink-700 disabled:opacity-60"
                >
                    <span x-show="!loading">Save message</span>
                    <span x-show="loading">Saving...</span>
                </button>

                <button
                    type="button"
                    @click="clearEditor()"
                    class="rounded-lg bg-gray-100 px-5 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-200"
                >
                    Clear
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="{{ asset('profile/js/profile-message-editor.js') }}?v={{ filemtime(public_path('profile/js/profile-message-editor.js')) }}"></script>
@endpush
