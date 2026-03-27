document.addEventListener('alpine:init', () => {
    Alpine.data('uploadVideoPage', (config = {}) => ({
        videos: [],
        uploading: false,
        isDragging: false,

        uploadUrl: config.uploadUrl,
        redirectUrl: config.redirectUrl,
        csrfToken: config.csrfToken,

        handleFiles(event) {
            this.addFiles(event.target.files);
        },

        handleDrop(event) {
            this.isDragging = false;
            this.addFiles(event.dataTransfer.files);
        },

        addFiles(files) {
            Array.from(files).forEach(file => {
                this.videos.push(file);
            });
        },

        async uploadVideos() {
            if (!this.videos.length) {
                return this.error('Select videos first');
            }

            this.uploading = true;

            const formData = new FormData();

            this.videos.forEach((file, i) => {
                formData.append(`videos[${i}]`, file);
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

                setTimeout(() => {
                    window.location.href = this.redirectUrl;
                }, 1000);

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
