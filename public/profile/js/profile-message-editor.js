document.addEventListener('alpine:init', () => {
    Alpine.data('profileMessageEditor', (config = {}) => ({
        content: config.initialContent || '',
        storeUrl: config.storeUrl || '',
        csrfToken: config.csrfToken || '',
        editor: null,
        loading: false,
        errors: {},

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

                const responseText = await response.text();
                console.log('Response status:', response.status);
                console.log('Response body:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON parse failed. Raw response:', responseText);
                    throw new Error('Server returned an invalid response (status ' + response.status + ').');
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
                if (this.editor) {
                    this.editor.setData(this.content);
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
            if (this.editor) {
                this.editor.setData('');
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
