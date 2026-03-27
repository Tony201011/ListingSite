document.addEventListener('alpine:init', () => {
    Alpine.data('uploadVideoPage', (config = {}) => ({

        selectedVideos: [],
        uploading: false,
        successMessage: '',
        errorMessage: '',
        isDragging: false,

        uploadUrl: config.uploadUrl || '',
        redirectUrl: config.redirectUrl || '',
        csrfToken: config.csrfToken || '',

        clearMessages() {
            this.successMessage = '';
            this.errorMessage = '';
        },

        // ✅ Toast helpers
        toastSuccess(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 1800
            });
        },

        toastError(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 2500
            });
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return `${bytes} B`;
            if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
            if (bytes < 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
            return `${(bytes / (1024 * 1024 * 1024)).toFixed(1)} GB`;
        },

        isDuplicate(file) {
            return this.selectedVideos.some(video =>
                video.name === file.name &&
                video.size === file.size &&
                video.lastModified === file.lastModified
            );
        },

        addFiles(files) {
            const incomingFiles = Array.from(files || []);

            incomingFiles.forEach(file => {
                if (this.isDuplicate(file)) {
                    this.toastError(`"${file.name}" is already selected`);
                    return;
                }

                this.selectedVideos.push({
                    key: `${file.name}-${file.size}-${file.lastModified}-${Math.random()}`,
                    file: file,
                    name: file.name,
                    size: file.size,
                    lastModified: file.lastModified,
                    previewUrl: URL.createObjectURL(file),
                });
            });

            if (this.$refs.videoInput) {
                this.$refs.videoInput.value = '';
            }
        },

        handleVideoChange(event) {
            this.clearMessages();
            this.addFiles(event.target.files);
        },

        handleDrop(event) {
            this.clearMessages();
            this.isDragging = false;
            this.addFiles(event.dataTransfer.files);
        },

        removeSelectedVideo(index) {
            const selectedVideo = this.selectedVideos[index];

            if (selectedVideo?.previewUrl) {
                URL.revokeObjectURL(selectedVideo.previewUrl);
            }

            this.selectedVideos.splice(index, 1);

            this.toastSuccess('Video removed');

            if (!this.selectedVideos.length && this.$refs.videoInput) {
                this.$refs.videoInput.value = '';
            }
        },

        clearSelection() {
            this.selectedVideos.forEach(video => {
                if (video.previewUrl) {
                    URL.revokeObjectURL(video.previewUrl);
                }
            });

            this.selectedVideos = [];

            this.toastSuccess('All videos cleared');

            if (this.$refs.videoInput) {
                this.$refs.videoInput.value = '';
            }
        },

        async uploadVideos() {
            this.clearMessages();

            if (!this.selectedVideos.length) {
                this.toastError('Please select at least one video.');
                return;
            }

            if (this.uploading) return;

            this.uploading = true;

            const formData = new FormData();

            this.selectedVideos.forEach((video, index) => {
                formData.append(`videos[${index}]`, video.file);
            });

            formData.append('_token', this.csrfToken);

            try {
                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    if (result.errors) {
                        const msg = Object.values(result.errors).flat().join('\n');
                        this.toastError(msg);
                        return;
                    }

                    this.toastError(result.message || 'Upload failed.');
                    return;
                }

                this.toastSuccess(result.message || 'Videos uploaded successfully.');

                this.clearSelection();

                setTimeout(() => {
                    window.location.href = this.redirectUrl;
                }, 1200);

            } catch (error) {
                this.toastError(error.message || 'Something went wrong.');
            } finally {
                this.uploading = false;
            }
        }

    }));
});
