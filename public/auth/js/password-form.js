function passwordForm(routeUrl, csrfToken) {
    return {
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

            if (!this.form.new_password) {
                this.errors.new_password = 'New password is required.';
                return;
            }

            if (this.form.new_password.length < 8) {
                this.errors.new_password = 'Password must be at least 8 characters.';
                return;
            }

            const hasUpper = /[A-Z]/.test(this.form.new_password);
            const hasLower = /[a-z]/.test(this.form.new_password);
            const hasNumber = /[0-9]/.test(this.form.new_password);
            const hasSymbol = /[^A-Za-z0-9]/.test(this.form.new_password);

            if (!(hasUpper && hasLower && hasNumber && hasSymbol)) {
                this.errors.new_password = 'Use uppercase, lowercase, number and symbol for a stronger password.';
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

        generateRandomPassword(length = 16) {
            const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const lower = 'abcdefghijklmnopqrstuvwxyz';
            const numbers = '0123456789';
            const symbols = '!@#$%^&*()-_=+[]{}?';
            const all = upper + lower + numbers + symbols;

            let password = '';
            password += upper[Math.floor(Math.random() * upper.length)];
            password += lower[Math.floor(Math.random() * lower.length)];
            password += numbers[Math.floor(Math.random() * numbers.length)];
            password += symbols[Math.floor(Math.random() * symbols.length)];

            for (let i = password.length; i < length; i++) {
                password += all[Math.floor(Math.random() * all.length)];
            }

            return password.split('').sort(() => Math.random() - 0.5).join('');
        },

        generatePasswordPopup() {
            this.generatedPassword = this.generateRandomPassword();
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
            try {
                await navigator.clipboard.writeText(this.generatedPassword);
                this.copied = true;
                setTimeout(() => this.copied = false, 1500);
            } catch (e) {
            }
        },

        get passwordStrength() {
            let score = 0;
            const pwd = this.form.new_password;

            if (!pwd) {
                return { text: '', color: '', width: '0%' };
            }

            if (pwd.length >= 8) score++;
            if (/[A-Z]/.test(pwd)) score++;
            if (/[a-z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;

            if (score <= 2) return { text: 'Weak', color: 'bg-red-500', width: '33%' };
            if (score <= 4) return { text: 'Medium', color: 'bg-yellow-500', width: '66%' };
            return { text: 'Strong', color: 'bg-green-500', width: '100%' };
        },

        async submitForm() {
            this.loading = true;
            this.message = null;
            this.errors = {};

            try {
                const response = await fetch(routeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
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

                this.message = { type: 'success', text: data.message };
                this.form = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                };
            } catch (error) {
                this.message = { type: 'error', text: error.message };
            } finally {
                this.loading = false;
            }
        }
    };
}
