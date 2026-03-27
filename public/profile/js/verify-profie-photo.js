document.addEventListener('alpine:init', () => {
    Alpine.data('verifyPage', (config = {}) => ({
        isModalOpen: false,
        activeTab: 'files',
        selectedFiles: [],
        filePreviews: [],
        stream: null,
        uploading: false,

        uploadUrl: config.uploadUrl,
        csrfToken: config.csrfToken,

        openModal() {
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
            this.stopCamera();
        },

        switchTab(tab) {
            this.activeTab = tab;

            if (tab === 'camera') this.startCamera();
            else this.stopCamera();
        },

        handleFileSelect(e) {
            const files = Array.from(e.target.files);

            files.forEach(file => {
                this.selectedFiles.push(file);
                this.filePreviews.push(URL.createObjectURL(file));
            });
        },

        async startCamera() {
            if (this.stream) return;

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                this.$refs.video.srcObject = this.stream;
            } catch {
                this.error('Camera not available');
            }
        },

        stopCamera() {
            if (!this.stream) return;

            this.stream.getTracks().forEach(t => t.stop());
            this.stream = null;
        },

        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            canvas.getContext('2d').drawImage(video, 0, 0);

            const data = canvas.toDataURL();
            const file = this.dataURLtoFile(data, 'capture.png');

            this.selectedFiles.push(file);
            this.filePreviews.push(URL.createObjectURL(file));
        },

        dataURLtoFile(dataurl, filename) {
            const arr = dataurl.split(',');
            const mime = arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);

            while (n--) u8arr[n] = bstr.charCodeAt(n);

            return new File([u8arr], filename, { type: mime });
        },

        async uploadFiles() {
            if (!this.selectedFiles.length) {
                return this.error('Select photos first');
            }

            this.uploading = true;

            const formData = new FormData();

            this.selectedFiles.forEach((f, i) => {
                formData.append(`photos[${i}]`, f);
            });

            formData.append('_token', this.csrfToken);

            try {
                const res = await fetch(this.uploadUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (!res.ok) throw new Error(data.message);

                this.toast('Uploaded successfully');

                setTimeout(() => location.reload(), 1200);

            } catch (e) {
                this.error(e.message);
            } finally {
                this.uploading = false;
            }
        },

        toast(msg) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: msg,
                timer: 1500,
                showConfirmButton: false
            });
        },

        error(msg) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: msg
            });
        }
    }));
});
