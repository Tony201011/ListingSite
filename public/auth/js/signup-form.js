document.addEventListener('alpine:init', () => {
    Alpine.data('signupForm', (config = {}) => ({
        email: config.initial?.email || '',
        nickname: config.initial?.nickname || '',
        password: '',
        confirmPassword: '',
        mobile: config.initial?.mobile || '',
        suburb: config.initial?.suburb || '',
        ageConfirm: config.initial?.ageConfirm || false,

        showPassword: false,
        showConfirmPassword: false,

        searchResults: [],
        showResults: false,
        debounceTimer: null,
        suburbSelected: false,

        errors: {},

        validate() {
            this.errors = {};

            if (!this.email.includes('@')) this.errors.email = true;
            if (this.nickname.length < 3) this.errors.nickname = true;
            if (this.password.length < 8) this.errors.password = true;
            if (this.password !== this.confirmPassword) this.errors.confirmPassword = true;
            if (!/^04\d{8}$/.test(this.mobile)) this.errors.mobile = true;
            if (!this.suburbSelected) this.errors.suburb = true;
            if (!this.ageConfirm) this.errors.ageConfirm = true;

            return Object.keys(this.errors).length === 0;
        },

        submitForm(e) {
            if (!this.validate()) e.preventDefault();
        },

        generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
            this.password = Array.from({ length: 12 })
                .map(() => chars[Math.floor(Math.random() * chars.length)])
                .join('');

            this.confirmPassword = this.password;
        },

        handleSuburbInput() {
            this.suburbSelected = false;

            clearTimeout(this.debounceTimer);

            this.debounceTimer = setTimeout(async () => {
                if (this.suburb.length < 2) return;

                const res = await fetch(`/api/suburbs/search?q=${this.suburb}`);
                this.searchResults = await res.json();
                this.showResults = true;
            }, 300);
        },

        selectSuburb(item) {
            this.suburb = `${item.suburb}, ${item.state} ${item.postcode}`;
            this.suburbSelected = true;
            this.showResults = false;
        }
    }));
});
