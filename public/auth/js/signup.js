function signupForm(config = {}) {
    return {
        fieldOrder: ['email', 'mobile', 'password', 'confirmPassword'],
        email: config.email || '',
        mobile: config.mobile || '',
        password: '',
        confirmPassword: '',
        showPassword: false,
        showConfirmPassword: false,
        showPasswordPopup: false,
        generatedPassword: '',
        copied: false,
        prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        errors: {},
        touched: {
            email: false,
            mobile: false,
            password: false,
            confirmPassword: false,
        },
        highlightedField: null,
        highlightedFieldTimeout: null,

        init() {
            this.$nextTick(() => {
                this.scrollToFirstServerError();
            });
        },

        getFirstInvalidFieldKey() {
            return this.fieldOrder.find(field => this.errors[field]);
        },

        getFieldRef(field) {
            const refMap = {
                email: 'email',
                mobile: 'mobile',
                password: 'password',
                confirmPassword: 'confirmPassword',
                captcha: 'captcha',
            };

            return this.$refs[refMap[field]] || null;
        },

        getStickyOffset() {
            const stickyHeader = document.querySelector('header, nav, .navbar, .sticky, .fixed');
            const stickyHeaderHeight = stickyHeader ? stickyHeader.getBoundingClientRect().height : 0;
            return stickyHeaderHeight + 180;
        },

        scrollElementIntoView(element) {
            if (!element) return;

            const offset = this.getStickyOffset();
            const elementTop = element.getBoundingClientRect().top + window.pageYOffset;
            const scrollTop = elementTop - offset;

            window.scrollTo({
                top: Math.max(scrollTop, 0),
                behavior: this.prefersReducedMotion ? 'auto' : 'smooth',
            });
        },

        getFieldScrollTarget(field) {
            const input = this.getFieldRef(field);
            if (!input) return null;

            let label = null;
            if (input.id) {
                label = document.querySelector(`label[for="${input.id}"]`);
            }

            const fieldWrapper = input.closest('.min-w-0') || input.closest('.mb-6') || input.closest('.my-6') || input.closest('.flex') || input.parentElement;
            const wrapperLabel = fieldWrapper?.querySelector('label');

            return label || wrapperLabel || fieldWrapper || input;
        },

        highlightField(field) {
            const input = this.getFieldRef(field);
            if (!input) return;

            if (this.highlightedField && this.highlightedField !== input) {
                this.highlightedField.classList.remove('signup-invalid-focus');
            }

            clearTimeout(this.highlightedFieldTimeout);
            input.classList.add('signup-invalid-focus');
            this.highlightedField = input;

            this.highlightedFieldTimeout = setTimeout(() => {
                input.classList.remove('signup-invalid-focus');
                if (this.highlightedField === input) {
                    this.highlightedField = null;
                }
            }, 2200);
        },

        scrollAndFocusField(field) {
            const scrollTarget = this.getFieldScrollTarget(field);
            const input = this.getFieldRef(field);

            if (!scrollTarget) return;

            this.scrollElementIntoView(scrollTarget);
            this.highlightField(field);

            setTimeout(() => {
                if (input && typeof input.focus === 'function') {
                    input.focus({ preventScroll: true });
                }
            }, this.prefersReducedMotion ? 0 : 500);
        },

        scrollToFirstServerError() {
            const firstServerError = this.$el.querySelector('[data-server-error="true"]');
            if (!firstServerError) return;

            const field = firstServerError.dataset.field;

            if (field) {
                this.scrollAndFocusField(field);
            } else {
                this.scrollElementIntoView(firstServerError);
            }
        },

        validateEmail() {
            if (!this.email || !/^\S+@\S+\.\S+$/.test(this.email)) {
                this.errors.email = 'Valid email is required.';
            } else {
                delete this.errors.email;
            }
        },

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
            } else if (this.password !== this.confirmPassword) {
                this.errors.confirmPassword = 'Passwords do not match.';
            } else {
                delete this.errors.confirmPassword;
            }
        },

        validateMobile() {
            const ausMobile = /^04\d{8}$/;

            if (!this.mobile) {
                this.errors.mobile = 'Mobile number is required.';
            } else if (!ausMobile.test(this.mobile)) {
                this.errors.mobile = 'Only Australian mobile numbers in the format 04XXXXXXXX are allowed (e.g. 0412345678)';
            } else {
                delete this.errors.mobile;
            }
        },

        validate() {
            this.validateEmail();
            this.validateMobile();
            this.validatePassword();
            this.validateConfirmPassword();

            return Object.keys(this.errors).length === 0;
        },

        submitForm(e) {
            Object.keys(this.touched).forEach(key => {
                this.touched[key] = true;
            });

            if (!this.validate()) {
                e.preventDefault();

                this.$nextTick(() => {
                    const firstInvalid = this.getFirstInvalidFieldKey();

                    if (firstInvalid) {
                        this.scrollAndFocusField(firstInvalid);
                    }
                });

                return false;
            }

            return true;
        },

        generatePasswordPopup() {
            this.generatedPassword = window.PasswordTools.generateRandomPassword(16);
            this.copied = false;
            this.showPasswordPopup = true;
        },

        useGeneratedPassword() {
            this.password = this.generatedPassword;
            this.confirmPassword = this.generatedPassword;
            this.touched.password = true;
            this.touched.confirmPassword = true;
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
            }
        },

        get passwordStrength() {
            return window.PasswordTools.getPasswordStrength(this.password);
        },
    };
}
