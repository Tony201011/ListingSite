document.addEventListener('alpine:init', () => {
    Alpine.data('referralCopy', (config = {}) => ({
        code: config.code || '',
        link: config.link || '',
        buttonText: 'Copy',

        async copyCode() {
            if (!this.code) {
                this.showError('No referral code found.');
                return;
            }

            try {
                await navigator.clipboard.writeText(this.code);
                this.buttonText = 'Copied';
                this.showSuccess('Referral code copied successfully.');

                setTimeout(() => {
                    this.buttonText = 'Copy';
                }, 2000);
            } catch (error) {
                this.showError('Copy failed. Please try again.');
            }
        },

        async copyLink() {
            if (!this.link) {
                this.showError('No referral link found.');
                return;
            }

            try {
                await navigator.clipboard.writeText(this.link);
                this.showSuccess('Referral link copied successfully.');
            } catch (error) {
                this.showError('Copy failed. Please try again.');
            }
        },

        showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1800,
                showConfirmButton: false,
            });
        },

        showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
            });
        },
    }));
});
