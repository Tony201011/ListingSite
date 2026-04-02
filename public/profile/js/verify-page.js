function verifyPage(config) {
    return {
        uploadUrl: config.uploadUrl || '',
        deleteUrl: config.deleteUrl || '',
        csrfToken: config.csrfToken || '',

        isModalOpen: false,
        activeTab: 'files',
        isDragging: false,
        selectedFiles: [],
        capturedImage: '',
        stream: null,
        isUploading: false,

        openModal() {
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
            this.isDragging = false;
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

        handleFileSelect(event) {
            const files = Array.from(event.target.files || []);
            this.addFiles(files);

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const files = Array.from(event.dataTransfer.files || []);
            this.addFiles(files);
        },

        addFiles(files) {
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            const existingKeys = new Set(
                this.selectedFiles.map((file) => `${file.name}-${file.size}-${file.lastModified || 0}`)
            );

            for (const file of files) {
                if (!allowedTypes.includes(file.type)) {
                    continue;
                }

                if (file.size > 10 * 1024 * 1024) {
                    this.error('Each photo must be 10MB or smaller.');
                    continue;
                }

                const key = `${file.name}-${file.size}-${file.lastModified || 0}`;

                if (existingKeys.has(key)) {
                    continue;
                }

                if (this.selectedFiles.length >= 5) {
                    this.error('You can upload a maximum of 5 photos.');
                    break;
                }

                this.selectedFiles.push(file);
                existingKeys.add(key);
            }
        },

        removeSelectedFile(index) {
            this.selectedFiles.splice(index, 1);

            if (!this.selectedFiles.length && this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        clearSelectedFiles() {
            this.selectedFiles = [];

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        async startCamera() {
            if (this.stream) {
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.error('Camera is not supported on this device or browser.');
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' }
                });

                if (this.$refs.video) {
                    this.$refs.video.srcObject = this.stream;
                }
            } catch (err) {
                this.error('Camera access denied or not available.');
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
            const videoElement = this.$refs.video;
            const canvasElement = this.$refs.canvas;

            if (!videoElement || !canvasElement || !videoElement.videoWidth) {
                this.error('Camera is not ready yet.');
                return;
            }

            canvasElement.width = videoElement.videoWidth;
            canvasElement.height = videoElement.videoHeight;

            const context = canvasElement.getContext('2d');

            if (!context) {
                this.error('Unable to capture photo.');
                return;
            }

            context.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
            this.capturedImage = canvasElement.toDataURL('image/png');
        },

        dataURLtoFile(dataUrl, filename) {
            const arr = dataUrl.split(',');
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

        addCapturedPhotoToSelection() {
            if (!this.capturedImage) {
                this.error('No captured photo to add.');
                return;
            }

            if (this.selectedFiles.length >= 5) {
                this.error('You can upload a maximum of 5 photos.');
                return;
            }

            const fileName = `camera-capture-${Date.now()}.png`;
            const file = this.dataURLtoFile(this.capturedImage, fileName);
            this.selectedFiles.push(file);
            this.toast('Captured photo added to upload list.');
        },

        clearCapturedPhoto() {
            this.capturedImage = '';
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return `${bytes} B`;
            if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        },

        async uploadFiles() {
            if (!this.selectedFiles.length) {
                this.error('Please select at least one photo.');
                return;
            }

            this.isUploading = true;

            const formData = new FormData();
            this.selectedFiles.forEach((file) => {
                formData.append('photos[]', file);
            });

            try {
                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    throw new Error('Server returned an invalid response.');
                }

                if (!response.ok) {
                    if (data.errors) {
                        const firstErrorGroup = Object.values(data.errors)[0];
                        const firstError = Array.isArray(firstErrorGroup) ? firstErrorGroup[0] : data.message;
                        throw new Error(firstError || 'Upload failed.');
                    }

                    throw new Error(data.message || 'Upload failed.');
                }

                this.toast(data.message || 'Verification photos uploaded successfully.');
                this.selectedFiles = [];
                this.capturedImage = '';
                this.stopCamera();

                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } catch (err) {
                this.error(err.message || 'Something went wrong while uploading.');
            } finally {
                this.isUploading = false;
            }
        },

        async deletePhoto(path, index) {
            if (!path) {
                return;
            }

            const result = await Swal.fire({
                title: 'Delete photo?',
                text: 'Are you sure you want to delete this photo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#db2777',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(this.deleteUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ path, index })
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    throw new Error('Server returned an invalid response.');
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Photo delete failed.');
                }

                this.toast(data.message || 'Photo deleted successfully.');

                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } catch (err) {
                this.error(err.message || 'Something went wrong while deleting the photo.');
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
    };
}
