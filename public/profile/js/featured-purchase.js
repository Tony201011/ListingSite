document.addEventListener('alpine:init', () => {
    Alpine.data('adTierPurchase', (config = {}) => ({
        userCredits: Number(config.initialUserCredits || 0),
        purchaseUrl: config.purchaseUrl || '',
        purchaseCreditUrl: config.purchaseCreditUrl || '',
        packages: Array.isArray(config.packages) ? config.packages : [],
        showTopUp: false,
        tiers: Array.isArray(config.tiers)
            ? config.tiers.map(t => ({ ...t, selectedDays: 1 }))
            : [],
        loading: null,      // key of tier currently loading
        messages: {},       // { [tier.key]: string }
        messageTypes: {},   // { [tier.key]: 'success' | 'error' }

        async purchase(tier) {
            const totalCost = tier.costPerDay * tier.selectedDays;

            if (this.loading || this.userCredits < totalCost) return;

            this.loading = tier.key;
            delete this.messages[tier.key];

            try {
                const response = await fetch(this.purchaseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ tier: tier.key, days: tier.selectedDays }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }

                // Update expiry for the purchased tier
                const updated = this.tiers.find(t => t.key === tier.key);
                if (updated) {
                    updated.expiresAt = data.expires_at ?? null;
                }

                this.userCredits -= totalCost;
                this.showMessage(tier.key, data.message || 'Activated successfully!', 'success');
            } catch (error) {
                this.showMessage(tier.key, error.message, 'error');
            } finally {
                this.loading = null;
            }
        },

        showMessage(key, msg, type = 'success') {
            this.messages[key] = msg;
            this.messageTypes[key] = type;

            setTimeout(() => {
                delete this.messages[key];
            }, 6000);
        },
    }));

    // Legacy alias – kept for backward compatibility with any existing references
    Alpine.data('featuredPurchase', (config = {}) => Alpine.data('adTierPurchase')(config));
});
