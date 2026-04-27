document.addEventListener('alpine:init', () => {
    Alpine.data('onlineNowToggle', (config = {}) => ({
        enabled: Boolean(config.initialStatus),
        remainingUses: Number(config.initialRemainingUses || 0),
        expiresAt: config.initialExpiresAt || null,
        updateUrl: config.updateUrl || '',
        csrfToken: config.csrfToken || '',

        loading: false,
        message: '',
        messageType: 'success',
        countdown: '00:00',
        timer: null,

        init() {
            if (this.enabled && this.expiresAt) {
                this.startTimer();
            }

            window.addEventListener('beforeunload', () => {
                this.stopTimer();
            });
        },

        startTimer() {
            this.stopTimer();
            this.updateCountdown();

            this.timer = setInterval(() => {
                this.updateCountdown();
            }, 1000);
        },

        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        updateCountdown() {
            if (!this.expiresAt) {
                this.countdown = '00:00';
                return;
            }

            const now = Date.now();
            const expiry = new Date(this.expiresAt).getTime();
            const diff = expiry - now;

            if (diff <= 0) {
                this.enabled = false;
                this.expiresAt = null;
                this.countdown = '00:00';
                this.stopTimer();

                this.showMessage('Your session has ended.', 'success');

                return;
            }

            const totalSeconds = Math.floor(diff / 1000);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;

            this.countdown =
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
                this.remainingUses = data.remaining_uses ?? this.remainingUses;
                this.expiresAt = data.expires_at ?? null;

                if (this.enabled && this.expiresAt) {
                    this.startTimer();
                } else {
                    this.stopTimer();
                    this.countdown = '00:00';
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
