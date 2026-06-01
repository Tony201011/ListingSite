document.addEventListener('alpine:init', () => {
    Alpine.data('availableToggle', (config = {}) => ({
        enabled: Boolean(config.initialStatus),
        startedAt: config.initialStartedAt || null,
        blockedBalance: Boolean(config.initialBlockedBalance),
        updateUrl: config.updateUrl || '',
        loading: false,
        message: '',
        messageType: 'success',

        init() {
            // Persistent manual toggle; no countdown timer required.
        },

        async toggleStatus() {
            if (this.loading || !this.updateUrl) return;

            this.loading = true;
            this.message = '';

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        status: this.enabled ? 'offline' : 'online'
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }

                this.enabled = data.status === 'online';
                this.startedAt = data.online_started_at ?? null;

                this.showMessage(data.message || 'Updated successfully');

            } catch (error) {
                this.showMessage(error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;

            setTimeout(() => {
                this.message = '';
            }, 3000);
        }
    }));
});
