function hideToggle(config = {}) {
    return {
        enabled: !Boolean(config.initialStatus),
        loading: false,
        updateUrl: config.updateUrl || '',
        csrfToken: config.csrfToken || '',

        async toggleStatus() {
            if (this.loading || !this.updateUrl) {
                return;
            }

            this.loading = true;

            const newDbStatus = this.enabled ? 'show' : 'hide';

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        status: newDbStatus,
                    }),
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    throw new Error('Server returned an invalid response.');
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                this.enabled = data.status === 'hide';
                this.toast(data.message || 'Profile status updated successfully.');
            } catch (error) {
                this.error(error.message || 'Something went wrong.');
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
    };
}
