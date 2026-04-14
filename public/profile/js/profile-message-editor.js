// Store Quill instance outside Alpine's reactive proxy to avoid conflicts
let quillEditorInstance = null;

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
            return quillEditorInstance;
        },

        initEditor() {
            this.$nextTick(() => {
                const el = this.$refs.editor;

                if (!el || quillEditorInstance) {
                    return;
                }

                quillEditorInstance = new Quill(el, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link', 'blockquote'],
                            ['clean']
                        ]
                    },
                    placeholder: 'Write your profile message...'
                });

                if (this.content) {
                    quillEditorInstance.clipboard.dangerouslyPasteHTML(0, this.content);
                }

                quillEditorInstance.on('text-change', () => {
                    this.content = quillEditorInstance.root.innerHTML;
                    this.errors = {};
                });
            });
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
                ed.setContents([]);
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
