document.addEventListener('alpine:init', () => {
    Alpine.data('profileMessageEditor', (config = {}) => ({
        content: config.initialContent || '',
        storeUrl: config.storeUrl || '',
        csrfToken: config.csrfToken || '',
        editor: null,
        loading: false,
        errors: {},
        message: {
            type: '',
            text: ''
        },

        init() {
            this.initEditor();
        },

        async initEditor() {
            await this.$nextTick();

            if (!this.$refs.editor || this.editor) {
                return;
            }

            try {
                const editor = await ClassicEditor.create(this.$refs.editor, {
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
                    placeholder: 'Write your profile message...'
                });

                this.editor = editor;
                editor.setData(this.content || '');

                editor.model.document.on('change:data', () => {
                    this.content = editor.getData();
                    this.errors = {};
                    this.message = { type: '', text: '' };
                });
            } catch (error) {
                console.error('CKEditor initialization error:', error);
                this.message = {
                    type: 'error',
                    text: 'Unable to load the editor.'
                };
            }
        },

        async saveMessage() {
            if (this.loading || !this.storeUrl) {
                return;
            }

            this.loading = true;
            this.errors = {};
            this.message = { type: '', text: '' };

            try {
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
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
    }));
});
