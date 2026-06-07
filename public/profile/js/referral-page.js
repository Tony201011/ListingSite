document.addEventListener('alpine:init', () => {
    Alpine.data('referralPage', (config = {}) => ({
        referralCode: config.referralCode || '',
        referralLink: config.referralLink || '',
        codeButtonText: 'Copy code',
        linkButtonText: 'Copy link',

        async copyCode() {
            if (!this.referralCode) {
                this.error('No referral code found');
                return;
            }

            try {
                await navigator.clipboard.writeText(this.referralCode);

                this.codeButtonText = 'Copied';

                this.toast('Referral code copied successfully');

                setTimeout(() => {
                    this.codeButtonText = 'Copy code';
                }, 2000);
            } catch {
                this.error('Copy failed. Try manually.');
            }
        },

        async copyLink() {
            if (!this.referralLink) {
                this.error('No referral link found');
                return;
            }

            try {
                await navigator.clipboard.writeText(this.referralLink);

                this.linkButtonText = 'Copied';

                this.toast('Referral link copied successfully');

                setTimeout(() => {
                    this.linkButtonText = 'Copy link';
                }, 2000);

            } catch {
                this.error('Copy failed. Try manually.');
            }
        },

        toast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                timer: 1500,
                showConfirmButton: false
            });
        },

        error(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }
    }));
});
