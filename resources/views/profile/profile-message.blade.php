@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="profileMessageEditor()"
    x-init="initEditor()"
>
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Profile message</h1>
            <p class="text-gray-600 mb-6">Set a short announcement for promotions, links, or important updates.</p>

            <div x-ref="editor" class="prose max-w-none"></div>

            <input type="hidden" x-model="content">

            <template x-if="message.text">
                <div
                    class="mt-4 rounded-lg px-4 py-3 text-sm"
                    :class="message.type === 'success'
                        ? 'bg-green-50 text-green-700 border border-green-200'
                        : 'bg-red-50 text-red-700 border border-red-200'"
                >
                    <span x-text="message.text"></span>
                </div>
            </template>

            <template x-if="errors.message">
                <div class="mt-4 rounded-lg px-4 py-3 text-sm bg-red-50 text-red-700 border border-red-200">
                    <span x-text="errors.message[0]"></span>
                </div>
            </template>

            <div class="mt-6 flex gap-3">
                <button
                    type="button"
                    @click="saveMessage"
                    :disabled="loading"
                    class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition disabled:opacity-60"
                >
                    <span x-show="!loading">Save message</span>
                    <span x-show="loading">Saving...</span>
                </button>

                <button
                    type="button"
                    @click="clearEditor"
                    class="px-5 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition"
                >
                    Clear
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

<script>
    function profileMessageEditor() {
        return {
            content: @json($message->message ?? ''),
            editor: null,
            loading: false,
            errors: {},
            message: {
                type: '',
                text: ''
            },

            async initEditor() {
                await this.$nextTick();

                ClassicEditor
                    .create(this.$refs.editor, {
                        toolbar: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'underline',
                            '|',
                            'bulletedList',
                            'numberedList',
                            '|',
                            'link',
                            'blockQuote',
                            '|',
                            'undo',
                            'redo'
                        ],
                        heading: {
                            options: [
                                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                            ]
                        },
                        placeholder: 'Write your profile message...',
                    })
                    .then(editor => {
                        this.editor = editor;
                        editor.setData(this.content || '');

                        editor.model.document.on('change:data', () => {
                            this.content = editor.getData();
                            this.errors = {};
                            this.message = { type: '', text: '' };
                        });
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                    });
            },

            async saveMessage() {
                this.loading = true;
                this.errors = {};
                this.message = { type: '', text: '' };

                try {
                    const response = await fetch('{{ route('storeProfileMessage') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            message: this.content
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            this.errors = data.errors;
                        } else {
                            this.message = {
                                type: 'error',
                                text: data.message || 'Something went wrong.'
                            };
                        }
                        return;
                    }

                    this.message = {
                        type: 'success',
                        text: data.message || 'Profile message saved successfully.'
                    };
                } catch (error) {
                    this.message = {
                        type: 'error',
                        text: 'Unable to save profile message.'
                    };
                } finally {
                    this.loading = false;
                }
            },

            clearEditor() {
                if (this.editor) {
                    this.editor.setData('');
                }
                this.content = '';
                this.errors = {};
                this.message = { type: '', text: '' };
            }
        };
    }
</script>

<style>
    .ck-editor__editable {
        min-height: 250px;
        font-size: 1rem !important;
        line-height: 1.6 !important;
        color: #1f2937 !important;
        background-color: #ffffff !important;
        font-family: inherit !important;
    }

    .ck-editor__editable.ck-placeholder::before {
        color: #9ca3af !important;
        font-style: normal !important;
        opacity: 1;
    }

    .ck-content h1 {
        font-size: 2em !important;
        font-weight: 700 !important;
        margin-bottom: 0.5em !important;
    }

    .ck-content h2 {
        font-size: 1.5em !important;
        font-weight: 600 !important;
        margin-bottom: 0.5em !important;
    }

    .ck-content h3 {
        font-size: 1.25em !important;
        font-weight: 600 !important;
        margin-bottom: 0.5em !important;
    }

    .ck-content a {
        color: #e04ecb !important;
        text-decoration: underline !important;
    }

    .ck-content a:hover {
        color: #c13ab0 !important;
    }

    .ck-content ul,
    .ck-content ol {
        padding-left: 2em !important;
        margin-bottom: 1em !important;
    }

    .ck-content blockquote {
        border-left: 4px solid #e04ecb !important;
        padding-left: 1em !important;
        margin-left: 0 !important;
        font-style: italic !important;
        color: #4b5563 !important;
    }
</style>
@endsection
