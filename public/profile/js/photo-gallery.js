document.addEventListener('alpine:init', () => {
    Alpine.data('photoGallery', (config = {}) => ({
        loading: false,
        successMessage: '',
        errorMessage: '',
        confirmDeleteId: null,

        photos: config.photos || [],
        setCoverUrl: config.setCoverUrl,
        deleteUrl: config.deleteUrl,
        csrfToken: config.csrfToken,

        sliderOpen: false,
        sliderIndex: 0,

        get coverPhoto() {
            return this.photos.find(p => p.is_primary) || null;
        },

        openSlider(index) {
            this.sliderIndex = index;
            this.sliderOpen = true;
        },

        closeSlider() {
            this.sliderOpen = false;
        },

        nextSlide() {
            if (this.photos.length > 1) {
                this.sliderIndex = (this.sliderIndex + 1) % this.photos.length;
            }
        },

        prevSlide() {
            if (this.photos.length > 1) {
                this.sliderIndex = (this.sliderIndex - 1 + this.photos.length) % this.photos.length;
            }
        },

        async setCover(id) {
            if (this.loading) return;

            this.loading = true;

            try {
                const res = await fetch(
                    this.setCoverUrl.replace('__ID__', id),
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await res.json();

                if (!res.ok) throw new Error(data.message);

                this.photos = this.photos.map(p => ({
                    ...p,
                    is_primary: p.id === id
                }));

                this.toast(data.message || 'Cover updated');

            } catch (e) {
                this.error(e.message);
            } finally {
                this.loading = false;
            }
        },

        async askRemove(id) {
            if (this.loading) return;

            const result = await Swal.fire({
                title: 'Delete this photo?',
                text: "You won't be able to recover it.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e04ecb',
                confirmButtonText: 'Yes, delete'
            });

            if (result.isConfirmed) {
                this.removePhoto(id);
            }
        },

        async removePhoto(id) {
            this.loading = true;

            try {
                const res = await fetch(
                    this.deleteUrl.replace('__ID__', id),
                    {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await res.json();

                if (!res.ok) throw new Error(data.message);

                const deletedIndex = this.photos.findIndex(p => p.id === id);

                this.photos = this.photos.filter(p => p.id !== id);

                if (!this.photos.some(p => p.is_primary) && this.photos.length > 0) {
                    this.photos[0].is_primary = true;
                }

                if (this.sliderOpen) {
                    if (!this.photos.length) {
                        this.closeSlider();
                    } else if (this.sliderIndex >= this.photos.length) {
                        this.sliderIndex = this.photos.length - 1;
                    } else if (deletedIndex !== -1 && this.sliderIndex > deletedIndex) {
                        this.sliderIndex--;
                    }
                }

                this.toast(data.message || 'Photo deleted');

            } catch (e) {
                this.error(e.message);
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
