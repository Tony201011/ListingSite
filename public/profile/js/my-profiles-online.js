document.addEventListener('alpine:init', () => {
    Alpine.data('profileOnlineToggle', (config = {}) => ({
        profileId: Number(config.profileId || 0),
        online: Boolean(config.initialStatus),
        updateUrl: config.updateUrl || '',
        csrfToken: config.csrfToken || '',

        loading: false,
        message: '',
        messageType: 'success',

        init() {
            window.profileOnlineSync?.subscribe?.((payload) => {
                if (Number(payload?.profileId) !== this.profileId) {
                    return;
                }

                this.online = payload.status === 'online';
            });
        },

        async toggleOnline() {
            if (this.loading || !this.updateUrl) return;

            this.loading = true;
            const newStatus = this.online ? 'offline' : 'online';

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                this.online = data.status === 'online';
                window.profileOnlineSync?.notify({
                    profileId: this.profileId,
                    status: data.status,
                });

                this.showMessage(data.message || 'Status updated.');
            } catch (error) {
                this.showMessage(error.message || 'Something went wrong.', 'error');
            } finally {
                this.loading = false;
            }
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => { this.message = ''; }, 3000);
        },
    }));
});
