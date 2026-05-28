function signupForm(config = {}) {
    return {
        fieldOrder: [
            'email',
            'nickname',
            'password',
            'confirmPassword',
            'mobile',
            'suburb',
            'ageConfirm'
        ],

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

        prefersReducedMotion: window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches,

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
            return this.$el.querySelector(
                `[data-error-container="${field}"]`
            );
        },

        /*
        |--------------------------------------------------------------------------
        | FIXED SCROLL FUNCTIONALITY
        |--------------------------------------------------------------------------
        */

        getStickyOffset() {
            const stickyHeader = document.querySelector(
                'header, nav, .navbar, .sticky, .fixed'
            );

            const stickyHeaderHeight = stickyHeader
                ? stickyHeader.getBoundingClientRect().height
                : 0;

            // Extra top spacing
            return stickyHeaderHeight + 180;
        },

        scrollElementIntoView(element) {
            if (!element) return;

            const offset = this.getStickyOffset();

            const elementTop =
                element.getBoundingClientRect().top +
                window.pageYOffset;

            const scrollTop = elementTop - offset;

            window.scrollTo({
                top: Math.max(scrollTop, 0),
                behavior: this.prefersReducedMotion
                    ? 'auto'
                    : 'smooth'
            });
        },

        getFieldScrollTarget(field) {
            const input = this.getFieldRef(field);

            if (!input) return null;

            // Find label using for=""
            let label = null;

            if (input.id) {
                label = document.querySelector(
                    `label[for="${input.id}"]`
                );
            }

            // Find closest field wrapper
            const fieldWrapper =
                input.closest('.min-w-0') ||
                input.closest('.mb-6') ||
                input.closest('.my-6') ||
                input.closest('.flex') ||
                input.parentElement;

            // Fallback label inside wrapper
            const wrapperLabel =
                fieldWrapper?.querySelector('label');

            return (
                label ||
                wrapperLabel ||
                fieldWrapper ||
                input
            );
        },

        highlightField(field) {
            const input = this.getFieldRef(field);

            if (!input) return;

            if (
                this.highlightedField &&
                this.highlightedField !== input
            ) {
                this.highlightedField.classList.remove(
                    'signup-invalid-focus'
                );
            }

            clearTimeout(this.highlightedFieldTimeout);

            input.classList.add('signup-invalid-focus');

            this.highlightedField = input;

            this.highlightedFieldTimeout = setTimeout(() => {
                input.classList.remove(
                    'signup-invalid-focus'
                );

                if (this.highlightedField === input) {
                    this.highlightedField = null;
                }
            }, 2200);
        },

        focusField(field) {
            const input = this.getFieldRef(field);

            if (
                !input ||
                typeof input.focus !== 'function'
            ) {
                return;
            }

            input.focus({
                preventScroll: true
            });
        },

        scrollAndFocusField(field) {
            const scrollTarget =
                this.getFieldScrollTarget(field);

            const input = this.getFieldRef(field);

            if (!scrollTarget) return;

            this.scrollElementIntoView(scrollTarget);

            this.highlightField(field);

            setTimeout(() => {
                if (
                    input &&
                    typeof input.focus === 'function'
                ) {
                    input.focus({
                        preventScroll: true
                    });
                }
            }, this.prefersReducedMotion ? 0 : 500);
        },

        /*
        |--------------------------------------------------------------------------
        | SERVER ERROR SCROLL
        |--------------------------------------------------------------------------
        */

        scrollToFirstServerError() {
            const firstServerError =
                this.$el.querySelector(
                    '[data-server-error="true"]'
                );

            if (!firstServerError) return;

            const field =
                firstServerError.dataset.field;

            if (field) {
                this.scrollAndFocusField(field);
            } else {
                this.scrollElementIntoView(
                    firstServerError
                );
            }
        },

        /*
        |--------------------------------------------------------------------------
        | VALIDATIONS
        |--------------------------------------------------------------------------
        */

        validateEmail() {
            if (
                !this.email ||
                !/^\S+@\S+\.\S+$/.test(this.email)
            ) {
                this.errors.email =
                    'Valid email is required.';
            } else {
                delete this.errors.email;
            }
        },

        validateNickname() {
            if (
                !this.nickname ||
                this.nickname.length < 3
            ) {
                this.errors.nickname =
                    'Nickname is required (min 3 chars).';
            } else {
                delete this.errors.nickname;
            }
        },

        validatePassword() {
            const error =
                window.PasswordTools.validatePassword(
                    this.password
                );

            if (error) {
                this.errors.password = error;
            } else {
                delete this.errors.password;
            }
        },

        validateConfirmPassword() {
            if (!this.confirmPassword) {
                this.errors.confirmPassword =
                    'Please confirm your password.';
            } else if (
                this.password !==
                this.confirmPassword
            ) {
                this.errors.confirmPassword =
                    'Passwords do not match.';
            } else {
                delete this.errors.confirmPassword;
            }
        },

        validateMobile() {
            const ausMobile = /^04\d{8}$/;

            if (!this.mobile) {
                this.errors.mobile =
                    'Mobile number is required.';
            } else if (
                !ausMobile.test(this.mobile)
            ) {
                this.errors.mobile =
                    'Only Australian mobile numbers in the format 04XXXXXXXX are allowed (e.g. 0412345678)';
            } else {
                delete this.errors.mobile;
            }
        },

        validateSuburb() {
            if (
                !this.suburb ||
                this.suburb.trim() === ''
            ) {
                this.errors.suburb =
                    'Suburb is required.';
            } else if (!this.suburbSelected) {
                this.errors.suburb =
                    'Please choose a location from the dropdown list.';
            } else {
                delete this.errors.suburb;
            }
        },

        validateAgeConfirm() {
            if (!this.ageConfirm) {
                this.errors.ageConfirm =
                    'You must confirm you are 18+';
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

            return (
                Object.keys(this.errors).length === 0
            );
        },

        /*
        |--------------------------------------------------------------------------
        | SUBMIT FORM
        |--------------------------------------------------------------------------
        */

        submitForm(e) {
            Object.keys(this.touched).forEach(key => {
                this.touched[key] = true;
            });

            if (!this.validate()) {
                e.preventDefault();

                this.$nextTick(() => {
                    const firstInvalid =
                        this.getFirstInvalidFieldKey();

                    if (firstInvalid) {
                        this.scrollAndFocusField(
                            firstInvalid
                        );
                    }
                });

                return false;
            }

            return true;
        },

        /*
        |--------------------------------------------------------------------------
        | PASSWORD TOOLS
        |--------------------------------------------------------------------------
        */

        generatePasswordPopup() {
            this.generatedPassword =
                window.PasswordTools.generateRandomPassword(
                    16
                );

            this.copied = false;
            this.showPasswordPopup = true;
        },

        useGeneratedPassword() {
            this.password =
                this.generatedPassword;

            this.confirmPassword =
                this.generatedPassword;

            this.touched.password = true;
            this.touched.confirmPassword = true;

            this.validatePassword();
            this.validateConfirmPassword();

            this.showPasswordPopup = false;
        },

        async copyGeneratedPassword() {
            const copied =
                await window.PasswordTools.copyToClipboard(
                    this.generatedPassword
                );

            if (copied) {
                this.copied = true;

                setTimeout(() => {
                    this.copied = false;
                }, 1500);
            }
        },

        get passwordStrength() {
            return window.PasswordTools.getPasswordStrength(
                this.password
            );
        },

        /*
        |--------------------------------------------------------------------------
        | SUBURB SEARCH
        |--------------------------------------------------------------------------
        */

        handleSuburbInput() {
            if (
                this.initialSuburb &&
                this.suburb === this.initialSuburb
            ) {
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

        sanitizeTextValue(value) {
            if (typeof value !== 'string') return null;

            const normalized = value.trim();
            if (!normalized) return null;

            const lower = normalized.toLowerCase();
            if (lower === 'null' || lower === 'undefined') return null;

            return normalized;
        },

        sanitizePostcodeValue(value) {
            const normalized = this.sanitizeTextValue(value);
            if (!normalized) return null;

            return /^\d{4}$/.test(normalized) ? normalized : null;
        },

        sanitizeSuburbItem(item) {
            if (!item || typeof item !== 'object') return null;

            const suburb = this.sanitizeTextValue(item.suburb);
            const state = this.sanitizeTextValue(item.state);
            if (!suburb || !state) return null;

            return {
                suburb,
                state,
                postcode: this.sanitizePostcodeValue(item.postcode)
            };
        },

        formatSuburbLabel(item) {
            const sanitizedItem = this.sanitizeSuburbItem(item);
            if (!sanitizedItem) return '';

            return sanitizedItem.postcode
                ? `${sanitizedItem.suburb}, ${sanitizedItem.state} ${sanitizedItem.postcode}`
                : `${sanitizedItem.suburb}, ${sanitizedItem.state}`;
        },

        sanitizeSuburbResults(data) {
            if (!Array.isArray(data)) return [];

            return data
                .map(item => this.sanitizeSuburbItem(item))
                .filter(Boolean);
        },

        searchSuburbs() {
            clearTimeout(this.debounceTimer);

            if (
                !this.suburb ||
                this.suburb.trim().length < 2
            ) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }

            this.debounceTimer = setTimeout(() => {
                this.searching = true;

                fetch(
                    `/api/suburbs/search?q=${encodeURIComponent(
                        this.suburb.trim()
                    )}`
                )
                    .then(res => {
                        if (!res.ok) {
                            throw new Error(
                                'Failed to fetch suburbs'
                            );
                        }

                        return res.json();
                    })
                    .then(data => {
                        this.searchResults = this.sanitizeSuburbResults(data);

                        this.showResults =
                            this.searchResults.length > 0;
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
            const label = this.formatSuburbLabel(item);
            if (!label) {
                this.showResults = false;
                this.searchResults = [];
                return;
            }

            this.suburb = label;

            this.suburbSelected = true;
            this.showResults = false;
            this.searchResults = [];

            this.touched.suburb = true;

            this.validateSuburb();
        }
    };
}
