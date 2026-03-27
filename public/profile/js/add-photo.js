document.addEventListener('alpine:init', () => {
    Alpine.data('addPhotoPage', (config = {}) => ({
        isModalOpen: false,
        activeTab: 'files',
        isDragging: false,
        selectedFiles: [],
        filePreviews: [],
        stream: null,
        uploading: false,
        sliderOpen: false,
        sliderIndex: 0,
        successMessage: '',
        errorMessage: '',
        uploadUrl: config.uploadUrl || '',
        photosUrl: config.photosUrl || '',
        csrfToken: config.csrfToken || '',

        init() {
            window.addEventListener('beforeunload', () => {
                this.cleanupResources();
            });
        },

        clearMessages() {
            this.successMessage = '';
            this.errorMessage = '';
        },

        openModal() {
            this.isModalOpen = true;
            this.clearMessages();
        },

        closeModal() {
            this.isModalOpen = false;
            this.stopCamera();
        },

        switchTab(tab) {
            this.activeTab = tab;

            if (tab === 'camera') {
                this.startCamera();
            } else {
                this.stopCamera();
            }
        },

        openFilePicker() {
            if (this.$refs.fileInput) {
                this.$refs.fileInput.click();
            }
        },

        isFileDuplicate(newFile) {
            return this.selectedFiles.some((existingFile) =>
                existingFile.name === newFile.name &&
                existingFile.size === newFile.size &&
                existingFile.lastModified === newFile.lastModified
            );
        },

        addFiles(files) {
            const imageFiles = files.filter((file) => file.type.startsWith('image/'));
            const uniqueFiles = imageFiles.filter((file) => !this.isFileDuplicate(file));

            uniqueFiles.forEach((file) => {
                this.selectedFiles.push(file);
                this.filePreviews.push(URL.createObjectURL(file));
            });
        },

        handleFileSelect(event) {
            this.clearMessages();
            const files = Array.from(event.target.files || []);
            this.addFiles(files);

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        handleDrop(event) {
            this.clearMessages();
            this.isDragging = false;

            const files = Array.from(event.dataTransfer?.files || []);
            this.addFiles(files);
        },

        removeSelectedFile(index) {
            if (this.filePreviews[index]) {
                URL.revokeObjectURL(this.filePreviews[index]);
            }

            this.filePreviews.splice(index, 1);
            this.selectedFiles.splice(index, 1);

            if (this.sliderIndex >= this.filePreviews.length) {
                this.sliderIndex = Math.max(this.filePreviews.length - 1, 0);
            }

            if (!this.filePreviews.length) {
                this.closeSlider();
            }

            if (!this.selectedFiles.length && this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        clearSelectedFiles() {
            this.filePreviews.forEach((url) => URL.revokeObjectURL(url));
            this.filePreviews = [];
            this.selectedFiles = [];
            this.sliderIndex = 0;

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        async startCamera() {
            this.clearMessages();

            if (this.stream) {
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.errorMessage = 'Camera is not supported on this device/browser.';
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: true });

                if (this.$refs.video) {
                    this.$refs.video.srcObject = this.stream;
                }
            } catch (error) {
                this.errorMessage = 'Camera access denied or not available.';
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach((track) => track.stop());
                this.stream = null;
            }

            if (this.$refs.video) {
                this.$refs.video.srcObject = null;
            }
        },

        capturePhoto() {
            this.clearMessages();

            const video = this.$refs.video;
            const canvas = this.$refs.canvas;

            if (!video || !canvas || !video.videoWidth) {
                this.errorMessage = 'Camera is not ready yet.';
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext('2d');

            if (!context) {
                this.errorMessage = 'Unable to capture photo.';
                return;
            }

            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataURL = canvas.toDataURL('image/png');
            const file = this.dataURLtoFile(dataURL, `capture_${Date.now()}.png`);

            if (!this.isFileDuplicate(file)) {
                this.selectedFiles.push(file);
                this.filePreviews.push(URL.createObjectURL(file));
            }

            this.successMessage = 'Photo captured successfully.';
        },

        dataURLtoFile(dataurl, filename) {
            const arr = dataurl.split(',');
            const mimeMatch = arr[0].match(/:(.*?);/);
            const mime = mimeMatch ? mimeMatch[1] : 'image/png';
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);

            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }

            return new File([u8arr], filename, { type: mime });
        },

        async uploadFiles() {
            this.clearMessages();

            if (!this.selectedFiles.length) {
                this.errorMessage = 'Please select at least one image.';
                return;
            }

            if (this.uploading) {
                return;
            }

            if (!this.uploadUrl) {
                this.errorMessage = 'Upload URL is missing.';
                return;
            }

            this.uploading = true;

            const formData = new FormData();

            this.selectedFiles.forEach((file, index) => {
                formData.append(`photos[${index}]`, file);
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
                        const allErrors = Object.values(result.errors).flat().join('\n');
                        throw new Error(allErrors);
                    }

                    throw new Error(result.message || 'Upload failed.');
                }

                this.successMessage = result.message || 'Upload successful.';
                this.clearSelectedFiles();

                setTimeout(() => {
                    this.closeModal();

                    if (this.photosUrl) {
                        window.location.href = this.photosUrl;
                    }
                }, 1200);
            } catch (error) {
                this.errorMessage = error.message || 'Something went wrong.';
            } finally {
                this.uploading = false;
            }
        },

        openSlider(index) {
            if (!this.filePreviews.length) {
                return;
            }

            this.sliderIndex = index;
            this.sliderOpen = true;
        },

        closeSlider() {
            this.sliderOpen = false;
        },

        nextSlide() {
            if (this.filePreviews.length > 1) {
                this.sliderIndex = (this.sliderIndex + 1) % this.filePreviews.length;
            }
        },

        prevSlide() {
            if (this.filePreviews.length > 1) {
                this.sliderIndex = (this.sliderIndex - 1 + this.filePreviews.length) % this.filePreviews.length;
            }
        },

        cleanupResources() {
            this.stopCamera();
            this.filePreviews.forEach((url) => URL.revokeObjectURL(url));
        }
    }));
});
