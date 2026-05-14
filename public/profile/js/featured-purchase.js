document.addEventListener('alpine:init', () => {
    Alpine.data('featuredPurchase', (config = {}) => ({
        isFeatured: Boolean(config.initialIsFeatured),
        expiresAt: config.initialExpiresAt,
        userCredits: Number(config.initialUserCredits || 0),
        creditCost: Number(config.creditCost || 0),
        durationDays: Number(config.durationDays || 7),
        purchaseUrl: config.purchaseUrl || '',
        loading: false,
        message: '',
        messageType: 'success',

        get formattedExpiry() {
            if (!this.expiresAt) return '';
            const d = new Date(this.expiresAt);
            return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) +
                ' at ' + d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
        },

        async purchase() {
            if (this.loading || this.userCredits < this.creditCost) return;

            this.loading = true;
            this.message = '';

            try {
                const response = await fetch(this.purchaseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }

                this.isFeatured = Boolean(data.is_featured);
                this.expiresAt = data.expires_at ?? null;
                this.userCredits -= this.creditCost;

                this.showMessage(data.message || 'Featured activated successfully!');
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
            }, 5000);
        },
    }));
});
