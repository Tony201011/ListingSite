// Store CKEditor instances outside Alpine's reactive proxy to avoid conflicts
const editorInstances = new WeakMap();

document.addEventListener('alpine:init', () => {
    Alpine.data('profileMessageEditor', (config = {}) => ({
        content: config.initialContent || '',
        storeUrl: config.storeUrl || '',
        csrfToken: config.csrfToken || '',
        loading: false,
        errors: {},

        init() {
            this.initEditor();
        },

        getEditor() {
            return editorInstances.get(this.$refs.editor);
        },

        async initEditor() {
            await this.$nextTick();

            if (!this.$refs.editor || editorInstances.has(this.$refs.editor)) {
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

                editorInstances.set(this.$refs.editor, editor);
                editor.setData(this.content || '');

                editor.model.document.on('change:data', () => {
                    this.content = editor.getData();
                    this.errors = {};
                });
            } catch (error) {
                console.error('CKEditor initialization error:', error);
                this.error('Unable to load the editor.');
            }
        },

        async saveMessage() {
            if (this.loading || !this.storeUrl) {
                return;
            }

            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        message: this.content
                    })
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    throw new Error('Server returned an invalid response.');
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.error(data.message || 'Something went wrong.');
                    }
                    return;
                }

                // Update editor with saved content so it stays visible
                const ed = this.getEditor();
                if (ed) {
                    ed.setData(this.content);
                }

                this.toast(data.message || 'Profile message saved successfully.');
            } catch (error) {
                console.error('Save error:', error);
                this.error(error.message || 'Unable to save profile message.');
            } finally {
                this.loading = false;
            }
        },

        clearEditor() {
            const ed = this.getEditor();
            if (ed) {
                ed.setData('');
            }

            this.content = '';
            this.errors = {};
        },

        toast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                timer: 1800,
                showConfirmButton: false
            });
        },

        error(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }
    }));
});
