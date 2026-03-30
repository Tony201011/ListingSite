function resetPasswordForm() {
    return {
        password: '',
        confirmPassword: '',
        showPassword: false,
        showConfirmPassword: false,
        showPasswordPopup: false,
        generatedPassword: '',
        copied: false,
        errors: {},

        validatePassword() {
            const error = window.PasswordTools.validatePassword(this.password);

            if (error) {
                this.errors.password = error;
            } else {
                delete this.errors.password;
            }
        },

        validateConfirmPassword() {
            if (!this.confirmPassword) {
                this.errors.confirmPassword = 'Please confirm your password.';
                return;
            }

            if (this.password !== this.confirmPassword) {
                this.errors.confirmPassword = 'Passwords do not match.';
                return;
            }

            delete this.errors.confirmPassword;
        },

        submitForm(e) {
            this.validatePassword();
            this.validateConfirmPassword();

            if (this.errors.password || this.errors.confirmPassword) {
                e.preventDefault();
            }
        },

        generatePasswordPopup() {
            this.generatedPassword = window.PasswordTools.generateRandomPassword(16);
            this.copied = false;
            this.showPasswordPopup = true;
        },

        useGeneratedPassword() {
            this.password = this.generatedPassword;
            this.confirmPassword = this.generatedPassword;
            this.validatePassword();
            this.validateConfirmPassword();
            this.showPasswordPopup = false;
        },

        async copyGeneratedPassword() {
            const copied = await window.PasswordTools.copyToClipboard(this.generatedPassword);

            if (copied) {
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 1500);
            } else {
                this.copied = false;
            }
        },

        get passwordStrength() {
            return window.PasswordTools.getPasswordStrength(this.password);
        }
    };
}
