document.addEventListener('alpine:init', () => {
    Alpine.data('referralPage', (config = {}) => ({
        referralLink: config.referralLink || '',
        buttonText: 'Copy link',

        async copyLink() {
            if (!this.referralLink) {
                this.error('No referral link found');
                return;
            }

            try {
                await navigator.clipboard.writeText(this.referralLink);

                this.buttonText = 'Copied';

                this.toast('Referral link copied successfully');

                setTimeout(() => {
                    this.buttonText = 'Copy link';
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
