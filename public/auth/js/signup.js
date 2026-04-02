function signupForm(config = {}) {
    return {
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
            this.validateSuburb();
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
            if (!this.suburb || this.suburb.trim().length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }

            clearTimeout(this.debounceTimer);

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
