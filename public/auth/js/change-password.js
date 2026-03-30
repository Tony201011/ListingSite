function passwordForm(config) {
    return {
        updateUrl: config.updateUrl,
        csrfToken: config.csrfToken,

        form: {
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        },

        showPassword: false,
        showConfirmPassword: false,
        showPasswordPopup: false,
        generatedPassword: '',
        copied: false,
        loading: false,
        errors: {},
        message: null,

        validateNewPassword() {
            delete this.errors.new_password;

            const error = window.PasswordTools.validatePassword(this.form.new_password);
            if (error) {
                this.errors.new_password = error;
            }
        },

        validatePasswordMatch() {
            delete this.errors.new_password_confirmation;

            if (!this.form.new_password_confirmation) {
                this.errors.new_password_confirmation = ['Please confirm your password.'];
                return;
            }

            if (this.form.new_password !== this.form.new_password_confirmation) {
                this.errors.new_password_confirmation = ['Passwords do not match.'];
            }
        },

        clearFieldError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        generatePasswordPopup() {
            this.generatedPassword = window.PasswordTools.generateRandomPassword(16);
            this.copied = false;
            this.showPasswordPopup = true;
        },

        useGeneratedPassword() {
            this.form.new_password = this.generatedPassword;
            this.form.new_password_confirmation = this.generatedPassword;
            this.validateNewPassword();
            this.validatePasswordMatch();
            this.showPasswordPopup = false;
        },

        async copyGeneratedPassword() {
            const copied = await window.PasswordTools.copyToClipboard(this.generatedPassword);

            if (copied) {
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 1500);
            }
        },

        get passwordStrength() {
            return window.PasswordTools.getPasswordStrength(this.form.new_password);
        },

        async submitForm() {
            this.loading = true;
            this.message = null;
            this.errors = {};

            this.validateNewPassword();
            this.validatePasswordMatch();

            if (this.errors.new_password || this.errors.new_password_confirmation) {
                this.loading = false;
                return;
            }

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
                    text: data.message || 'Password updated successfully.'
                };

                this.form = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                };

                this.showPassword = false;
                this.showConfirmPassword = false;
                this.showPasswordPopup = false;
                this.generatedPassword = '';
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
