function emailForm(config) {
    return {
        updateUrl: config.updateUrl,
        csrfToken: config.csrfToken,
        currentEmail: config.currentEmail,

        form: {
            new_email: '',
            current_password: '',
        },

        loading: false,
        errors: {},
        message: null,

        clearFieldError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        async submitForm() {
            this.loading = true;
            this.message = null;
            this.errors = {};

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Something went wrong.');
                    }
                    return;
                }

                this.message = {
                    type: 'success',
                    text: data.message || 'Email updated successfully.'
                };

                this.form = {
                    new_email: '',
                    current_password: '',
                };

                setTimeout(() => {
                    window.location.href = data.redirect || '/my-profile';
                }, 1500);
            } catch (error) {
                this.message = {
                    type: 'error',
                    text: error.message || 'Something went wrong.'
                };
            } finally {
                this.loading = false;
            }
        }
    };
}
