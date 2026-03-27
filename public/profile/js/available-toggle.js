document.addEventListener('alpine:init', () => {
    Alpine.data('availableToggle', (config = {}) => ({
        enabled: Boolean(config.initialStatus),
        remainingUses: Number(config.initialRemainingUses || 0),
        expiresAt: config.initialExpiresAt,
        updateUrl: config.updateUrl || '',
        loading: false,
        message: '',
        messageType: 'success',
        countdown: '00:00:00',
        timer: null,

        init() {
            if (this.enabled && this.expiresAt) {
                this.startTimer();
            }

            // cleanup on page leave
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
                this.countdown = '00:00:00';
                return;
            }

            const diff = new Date(this.expiresAt).getTime() - Date.now();

            if (diff <= 0) {
                this.enabled = false;
                this.expiresAt = null;
                this.countdown = '00:00:00';
                this.stopTimer();

                this.showMessage('Your 2-hour session ended.', 'success');
                return;
            }

            const total = Math.floor(diff / 1000);
            const h = Math.floor(total / 3600);
            const m = Math.floor((total % 3600) / 60);
            const s = total % 60;

            this.countdown =
                String(h).padStart(2, '0') + ':' +
                String(m).padStart(2, '0') + ':' +
                String(s).padStart(2, '0');
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
                this.remainingUses = data.remaining_uses ?? this.remainingUses;
                this.expiresAt = data.expires_at ?? null;

                this.showMessage(data.message || 'Updated successfully');

                if (this.enabled && this.expiresAt) {
                    this.startTimer();
                } else {
                    this.stopTimer();
                    this.countdown = '00:00:00';
                }

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
