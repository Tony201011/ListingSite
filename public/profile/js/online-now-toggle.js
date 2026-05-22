document.addEventListener('alpine:init', () => {
    Alpine.data('onlineNowToggle', (config = {}) => ({
        enabled: Boolean(config.initialStatus),
        expiresAt: config.initialExpiresAt || null,
        onlineStartedAt: config.initialOnlineStartedAt || null,
        blockedBalance: Boolean(config.initialBlockedBalance),
        updateUrl: config.updateUrl || '',
        csrfToken: config.csrfToken || '',

        loading: false,
        message: '',
        messageType: 'success',
        elapsed: '00:00:00',
        timer: null,

        init() {
            if (this.enabled && this.onlineStartedAt) {
                this.startTimer();
            }

            this._beforeUnloadHandler = () => this.stopTimer();
            window.addEventListener('beforeunload', this._beforeUnloadHandler);
        },

        destroy() {
            this.stopTimer();
            if (this._beforeUnloadHandler) {
                window.removeEventListener('beforeunload', this._beforeUnloadHandler);
            }
        },

        startTimer() {
            this.stopTimer();
            this.updateElapsed();

            this.timer = setInterval(() => {
                this.updateElapsed();
            }, 1000);
        },

        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        updateElapsed() {
            if (!this.onlineStartedAt) {
                this.elapsed = '00:00:00';
                return;
            }

            const now = Date.now();
            const start = new Date(this.onlineStartedAt).getTime();
            const diff = Math.max(0, now - start);

            const totalSeconds = Math.floor(diff / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            this.elapsed =
                String(hours).padStart(2, '0') +
                ':' +
                String(minutes).padStart(2, '0') +
                ':' +
                String(seconds).padStart(2, '0');
        },

        async toggleStatus() {
            if (this.loading || !this.updateUrl) return;

            this.loading = true;

            const newStatus = this.enabled ? 'offline' : 'online';

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        status: newStatus
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                this.enabled = data.status === 'online';
                this.onlineStartedAt = data.online_started_at ?? null;

                if (this.enabled && this.onlineStartedAt) {
                    this.startTimer();
                } else {
                    this.stopTimer();
                    this.elapsed = '00:00:00';
                }

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

            setTimeout(() => {
                this.message = '';
            }, 3000);
        }
    }));
});
