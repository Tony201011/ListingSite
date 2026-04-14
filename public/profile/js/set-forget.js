document.addEventListener('alpine:init', () => {
    Alpine.data('setAndForget', (config = {}) => ({
        onlineNowEnabled: Boolean(config.initialOnlineNowEnabled),
        onlineNowDays: Array.isArray(config.initialOnlineNowDays) ? config.initialOnlineNowDays : [],
        onlineNowTime: config.initialOnlineNowTime || '',
        availableNowEnabled: Boolean(config.initialAvailableNowEnabled),
        availableNowDays: Array.isArray(config.initialAvailableNowDays) ? config.initialAvailableNowDays : [],
        availableNowTime: config.initialAvailableNowTime || '',
        saveUrl: config.saveUrl || '',
        csrfToken: config.csrfToken || '',
        loading: false,
        message: '',
        messageType: 'success',

        async save() {
            if (this.loading || !this.saveUrl) return;

            this.loading = true;
            this.message = '';

            try {
                const response = await fetch(this.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        online_now_enabled: this.onlineNowEnabled,
                        online_now_days: this.onlineNowDays,
                        online_now_time: this.onlineNowTime || null,
                        available_now_enabled: this.availableNowEnabled,
                        available_now_days: this.availableNowDays,
                        available_now_time: this.availableNowTime || null,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }

                this.showMessage(data.message || 'Settings saved successfully.');

            } catch (error) {
                this.showMessage(error.message || 'Something went wrong.', 'error');
            } finally {
                this.loading = false;
            }
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;

            setTimeout(() => {
                this.message = '';
            }, 4000);
        },
    }));
});
