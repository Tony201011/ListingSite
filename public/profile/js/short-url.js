document.addEventListener('alpine:init', () => {
    Alpine.data('shortUrlForm', (config = {}) => ({
        slug: config.initialSlug || '',
        originalSlug: config.initialSlug || '',
        baseUrl: config.baseUrl || '',
        updateUrl: config.updateUrl || '',
        csrfToken: config.csrfToken || '',

        saving: false,

        get fullUrl() {
            return `${this.baseUrl}/${this.slug}`;
        },

        clearMessages() {},

        async saveSlug() {
            if (this.slug === this.originalSlug) {
                this.toast('No changes to save');
                return;
            }

            this.saving = true;

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ slug: this.slug }),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        throw new Error(Object.values(data.errors)[0][0]);
                    }
                    throw new Error(data.message || 'Error saving');
                }

                this.originalSlug = data.slug;
                this.slug = data.slug;

                this.toast(data.message || 'Saved successfully');

            } catch (error) {
                this.error(error.message);
            } finally {
                this.saving = false;
            }
        },

        toast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                timer: 1500,
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
