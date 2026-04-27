document.addEventListener('alpine:init', () => {
    Alpine.data('videoGallery', (config = {}) => ({
        loading: false,
        successMessage: '',
        errorMessage: '',

        videos: config.videos || [],
        deleteUrl: config.deleteUrl,
        csrfToken: config.csrfToken,

        async askRemove(id) {
            if (this.loading) return;

            const result = await Swal.fire({
                title: 'Delete this video?',
                text: "You won't be able to recover it.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e04ecb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete'
            });

            if (result.isConfirmed) {
                this.removeVideo(id);
            }
        },

        async removeVideo(id) {
            this.loading = true;

            try {
                const response = await fetch(
                    this.deleteUrl.replace('__ID__', id),
                    {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                let result;
                try {
                    result = await response.json();
                } catch {
                    throw new Error('Server returned an unexpected response. Please refresh the page and try again.');
                }

                if (!response.ok) {
                    throw new Error(result.message || 'Delete failed');
                }

                this.videos = this.videos.filter(v => v.id !== id);

                this.toast(result.message || 'Video deleted');

            } catch (error) {
                this.error(error.message);
            } finally {
                this.loading = false;
            }
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
