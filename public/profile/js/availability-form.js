document.addEventListener('alpine:init', () => {
    Alpine.data('availabilityForm', (config = {}) => ({
        form: config.initialForm || {},
        updateUrl: config.updateUrl,
        csrfToken: config.csrfToken,

        loading: false,

        handleAllDay(day) {
            if (this.form[day].all_day) {
                this.form[day].from = '';
                this.form[day].to = '';
            }
        },

        buildPayload() {
            const availability = {};

            Object.keys(this.form).forEach(day => {
                availability[day] = {
                    enabled: this.form[day].enabled ? 1 : 0,
                    from: this.form[day].from,
                    to: this.form[day].to,
                    till_late: this.form[day].till_late ? 1 : 0,
                    all_day: this.form[day].all_day ? 1 : 0,
                    by_appointment: this.form[day].by_appointment ? 1 : 0,
                };
            });

            return { availability };
        },

        async submitForm() {
            this.loading = true;

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.buildPayload())
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Error saving');
                }

                this.toast(data.message || 'Saved successfully');

            } catch (error) {
                this.error(error.message);
            } finally {
                this.loading = false;
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
