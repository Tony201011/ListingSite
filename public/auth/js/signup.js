function signupForm(config = {}) {
    return {
        fieldOrder: ['email', 'nickname', 'password', 'confirmPassword', 'mobile', 'suburb', 'ageConfirm'],

        email: config.email || '',
        nickname: config.nickname || '',
        password: '',
        confirmPassword: '',
        mobile: config.mobile || '',
        suburb: config.suburb || '',
        ageConfirm: !!config.ageConfirm,

        showPassword: false,
        showConfirmPassword: false,
        showPasswordPopup: false,
        generatedPassword: '',
        copied: false,
        prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,

        searchResults: [],
        showResults: false,
        searching: false,
        debounceTimer: null,
        suburbSelected: !!config.suburb,
        initialSuburb: config.suburb || '',

        errors: {},
        touched: {
            email: false,
            nickname: false,
            password: false,
            confirmPassword: false,
            mobile: false,
            suburb: false,
            ageConfirm: false
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
                nickname: 'nickname',
                password: 'password',
                confirmPassword: 'confirmPassword',
                mobile: 'mobile',
                suburb: 'suburb',
                ageConfirm: 'ageConfirm',
                captcha: 'captcha'
            };

            return this.$refs[refMap[field]] || null;
        },

        getFieldErrorContainer(field) {
            return this.$el.querySelector(`[data-error-container="${field}"]`);
        },

        getStickyOffset() {
            const stickyHeader = document.querySelector(
                'header.sticky, header[class*="sticky"], header.fixed, header[class*="fixed"]'
            );
            const stickyHeaderHeight = stickyHeader ? stickyHeader.getBoundingClientRect().height : 0;

            return stickyHeaderHeight + 16;
        },

        scrollElementIntoView(element) {
            if (!element) {
                return;
            }

            const stickyOffset = this.getStickyOffset();
            const rect = element.getBoundingClientRect();
            const bottomSpacing = 16;

            if (rect.top >= stickyOffset && rect.bottom <= window.innerHeight - bottomSpacing) {
                return;
            }

            const rawTop = window.scrollY + rect.top - stickyOffset;
            const maxScrollTop = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);
            const top = Math.min(Math.max(rawTop, 0), maxScrollTop);

            window.scrollTo({
                top,
                behavior: this.prefersReducedMotion ? 'auto' : 'smooth'
            });
        },

        highlightField(field) {
            const input = this.getFieldRef(field);

            if (!input) {
                return;
            }

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

        focusField(field) {
            const input = this.getFieldRef(field);

            if (!input || typeof input.focus !== 'function') {
                return;
            }

            setTimeout(() => {
                input.focus({ preventScroll: true });
            }, this.prefersReducedMotion ? 0 : 250);
        },

        findFirstFallbackInvalidElement() {
            return this.$el.querySelector(
                '[data-server-error="true"], .is-invalid, .error, [aria-invalid="true"], input:invalid, select:invalid, textarea:invalid'
            );
        },

        getFieldScrollTarget(field) {
            const input = this.getFieldRef(field);
            const errorContainer = this.getFieldErrorContainer(field);
            const fieldGroup = errorContainer?.parentElement;

            const labelByFor = input?.id ? document.querySelector(`label[for="${input.id}"]`) : null;
            const labelInGroup = fieldGroup?.querySelector('label');

            return labelByFor || labelInGroup || fieldGroup || input || errorContainer;
        },

        scrollAndFocusField(field) {
            const scrollTarget = this.getFieldScrollTarget(field);

            if (!scrollTarget) {
                return;
            }

            this.scrollElementIntoView(scrollTarget);
            this.highlightField(field);
            this.focusField(field);
        },

        scrollToFirstServerError() {
            const firstServerError = this.$el.querySelector('[data-server-error="true"]');
            if (!firstServerError) {
                return;
            }

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

        validateNickname() {
            if (!this.nickname || this.nickname.length < 3) {
                this.errors.nickname = 'Nickname is required (min 3 chars).';
            } else {
                delete this.errors.nickname;
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

        validateSuburb() {
            if (!this.suburb || this.suburb.trim() === '') {
                this.errors.suburb = 'Suburb is required.';
            } else if (!this.suburbSelected) {
                this.errors.suburb = 'Please choose a location from the dropdown list, which appears while typing.';
            } else {
                delete this.errors.suburb;
            }
        },

        validateAgeConfirm() {
            if (!this.ageConfirm) {
                this.errors.ageConfirm = 'You must confirm you are 18+';
            } else {
                delete this.errors.ageConfirm;
            }
        },

        validate() {
            this.validateEmail();
            this.validateNickname();
            this.validatePassword();
            this.validateConfirmPassword();
            this.validateMobile();
            this.validateSuburb();
            this.validateAgeConfirm();

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
                        return;
                    }

                    const fallbackInvalid = this.findFirstFallbackInvalidElement();
                    const field = fallbackInvalid?.dataset?.field;

                    if (field) {
                        this.scrollAndFocusField(field);
                        return;
                    }

                    this.scrollElementIntoView(fallbackInvalid);
                });
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
            } else {
                this.copied = false;
            }
        },

        get passwordStrength() {
            return window.PasswordTools.getPasswordStrength(this.password);
        },

        handleSuburbInput() {
            if (this.initialSuburb && this.suburb === this.initialSuburb) {
                return;
            }
            this.suburbSelected = false;
            this.initialSuburb = '';
            this.searchSuburbs();
        },

        handleSuburbBlur() {
            setTimeout(() => {
                this.showResults = false;
                this.touched.suburb = true;
                this.validateSuburb();
            }, 200);
        },

        searchSuburbs() {
            clearTimeout(this.debounceTimer);

            if (!this.suburb || this.suburb.trim().length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }

            this.debounceTimer = setTimeout(() => {
                this.searching = true;

                fetch(`/api/suburbs/search?q=${encodeURIComponent(this.suburb.trim())}`)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Failed to fetch suburbs');
                        }
                        return res.json();
                    })
                    .then(data => {
                        this.searchResults = Array.isArray(data) ? data : [];
                        this.showResults = this.searchResults.length > 0;
                    })
                    .catch(() => {
                        this.searchResults = [];
                        this.showResults = false;
                    })
                    .finally(() => {
                        this.searching = false;
                    });
            }, 300);
        },

        selectSuburb(item) {
            this.suburb = `${item.suburb}, ${item.state} ${item.postcode}`;
            this.suburbSelected = true;
            this.showResults = false;
            this.searchResults = [];
            this.touched.suburb = true;
            this.validateSuburb();
        }
    };
}
