document.addEventListener('alpine:init', () => {
    Alpine.data('uploadVideoPage', (config = {}) => ({
        uploadUrl: config.uploadUrl || '',
        redirectUrl: config.redirectUrl || '',
        csrfToken: config.csrfToken || '',

        selectedVideos: [],
        uploading: false,
        successMessage: '',
        errorMessage: '',
        isDragging: false,

        clearMessages() {
            this.successMessage = '';
            this.errorMessage = '';
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            return (bytes / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
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
                if (this.isDuplicate(file)) return;

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
            if (this.selectedVideos[index] && this.selectedVideos[index].previewUrl) {
                URL.revokeObjectURL(this.selectedVideos[index].previewUrl);
            }

            this.selectedVideos.splice(index, 1);

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

            if (this.$refs.videoInput) {
                this.$refs.videoInput.value = '';
            }
        },

        async uploadVideos() {
            this.clearMessages();

            if (!this.selectedVideos.length) {
                this.errorMessage = 'Please select at least one video.';
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

                let result;
                try {
                    result = await response.json();
                } catch {
                    throw new Error('Server returned an unexpected response. Please refresh the page and try again.');
                }

                if (!response.ok) {
                    if (result.errors) {
                        const allErrors = Object.values(result.errors).flat().join('\n');
                        throw new Error(allErrors);
                    }

                    throw new Error(result.message || 'Upload failed.');
                }

                this.successMessage = result.message || 'Videos uploaded successfully.';
                this.clearSelection();

                setTimeout(() => {
                    window.location.href = this.redirectUrl;
                }, 1200);

            } catch (error) {
                this.errorMessage = error.message || 'Something went wrong.';
            } finally {
                this.uploading = false;
            }
        }
    }));
});
