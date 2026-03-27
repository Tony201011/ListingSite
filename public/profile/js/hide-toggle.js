function hideToggle(config = {}) {
    return {
        enabled: !Boolean(config.initialStatus),
        loading: false,
        message: '',
        messageType: 'success',
        updateUrl: config.updateUrl || '',
        csrfToken: config.csrfToken || '',

        async toggleStatus() {
            if (this.loading || !this.updateUrl) {
                return;
            }

            this.loading = true;
            this.message = '';

            const newDbStatus = this.enabled ? 'show' : 'hide';

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        status: newDbStatus,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                this.enabled = data.status === 'hide';
                this.message = data.message || 'Profile status updated successfully.';
                this.messageType = 'success';

                setTimeout(() => {
                    this.message = '';
                }, 3000);
            } catch (error) {
                this.message = error.message || 'Something went wrong.';
                this.messageType = 'error';
            } finally {
                this.loading = false;
            }
        }
    };
}
