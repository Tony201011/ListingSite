document.addEventListener('alpine:init', () => {
    Alpine.data('availabilityForm', (config = {}) => ({
        form: config.initialForm || {},
        updateUrl: config.updateUrl,
        csrfToken: config.csrfToken,

        loading: false,
        errors: {},

        init() {
            this.setupWatchers();
        },

        setupWatchers() {
            Object.keys(this.form).forEach(day => {
                this.$watch(`form.${day}.from`, (newVal, oldVal) => {
                    this.clearFieldError(day, 'from');
                    // Reset 'to' if it's no longer valid after 'from' changed
                    const to = this.form[day].to;
                    if (newVal && to && to <= newVal) {
                        this.form[day].to = '';
                    }
                    this.validateDay(day);
                });

                this.$watch(`form.${day}.to`, () => {
                    this.clearFieldError(day, 'to');
                    this.validateDay(day);
                });

                this.$watch(`form.${day}.enabled`, (value) => {
                    if (!value) {
                        this.clearDayErrors(day);
                    } else {
                        this.validateDay(day);
                    }
                });

                this.$watch(`form.${day}.all_day`, (value) => {
                    if (value) {
                        this.form[day].till_late = false;
                        this.form[day].by_appointment = false;
                        this.form[day].from = '';
                        this.form[day].to = '';
                        this.clearFieldError(day, 'from');
                        this.clearFieldError(day, 'to');
                    } else {
                        this.validateDay(day);
                    }
                });

                this.$watch(`form.${day}.till_late`, (value) => {
                    if (value) {
                        this.form[day].all_day = false;
                        this.form[day].by_appointment = false;
                        this.form[day].to = '';
                        this.clearFieldError(day, 'to');
                    }
                });

                this.$watch(`form.${day}.by_appointment`, (value) => {
                    if (value) {
                        this.form[day].all_day = false;
                        this.form[day].till_late = false;
                        this.form[day].from = '';
                        this.form[day].to = '';
                        this.clearFieldError(day, 'from');
                        this.clearFieldError(day, 'to');
                    }
                });
            });
        },

        handleOptionToggle(day, option) {
            const options = ['all_day', 'till_late', 'by_appointment'];

            if (this.form[day][option]) {
                // Uncheck the other two options
                options.forEach(opt => {
                    if (opt !== option) {
                        this.form[day][opt] = false;
                    }
                });

                // Clear from/to when all_day or by_appointment is selected
                if (option === 'all_day' || option === 'by_appointment') {
                    this.form[day].from = '';
                    this.form[day].to = '';
                    this.clearFieldError(day, 'from');
                    this.clearFieldError(day, 'to');
                } else if (option === 'till_late') {
                    this.form[day].to = '';
                    this.clearFieldError(day, 'to');
                }
            } else {
                this.validateDay(day);
            }
        },

        getToOptions(day) {
            const from = this.form[day]?.from;
            const allTimes = [];
            for (let i = 0; i <= 23; i++) {
                allTimes.push(String(i).padStart(2, '0') + ':00');
            }
            if (!from) return allTimes;
            return allTimes.filter(t => t > from);
        },

        getFieldError(day, field) {
            return this.errors?.[day]?.[field]?.[0] || '';
        },

        setFieldError(day, field, message) {
            if (!this.errors[day]) {
                this.errors[day] = {};
            }

            this.errors[day][field] = [message];
        },

        clearFieldError(day, field) {
            if (this.errors[day] && this.errors[day][field]) {
                delete this.errors[day][field];

                if (Object.keys(this.errors[day]).length === 0) {
                    delete this.errors[day];
                }
            }
        },

        clearDayErrors(day) {
            if (this.errors[day]) {
                delete this.errors[day];
            }
        },

        validateDay(day) {
            const item = this.form[day];

            this.clearFieldError(day, 'from');
            this.clearFieldError(day, 'to');

            if (!item.enabled) {
                return true;
            }

            if (item.all_day || item.by_appointment) {
                return true;
            }

            if (!item.from && item.to) {
                this.setFieldError(day, 'from', `Please select a start time for ${day}.`);
                return false;
            }

            if (item.from && !item.to && !item.till_late) {
                this.setFieldError(day, 'to', `Please select an end time for ${day}.`);
                return false;
            }

            if (item.from && item.to && item.to <= item.from) {
                this.setFieldError(day, 'to', `The to time must be later than the from time for ${day}.`);
                return false;
            }

            return true;
        },

        validateForm() {
            let isValid = true;

            Object.keys(this.form).forEach(day => {
                const valid = this.validateDay(day);
                if (!valid) {
                    isValid = false;
                }
            });

            return isValid;
        },

        buildPayload() {
            const availability = {};

            Object.keys(this.form).forEach(day => {
                availability[day] = {
                    enabled: this.form[day].enabled ? 1 : 0,
                    from: this.form[day].from || '',
                    to: this.form[day].to || '',
                    till_late: this.form[day].till_late ? 1 : 0,
                    all_day: this.form[day].all_day ? 1 : 0,
                    by_appointment: this.form[day].by_appointment ? 1 : 0,
                };
            });

            return { availability };
        },

        mapBackendErrors(serverErrors = {}) {
            this.errors = {};

            Object.keys(serverErrors).forEach(key => {
                const match = key.match(/^availability\.(.+?)\.(.+)$/);

                if (match) {
                    const day = match[1];
                    const field = match[2];

                    if (!this.errors[day]) {
                        this.errors[day] = {};
                    }

                    this.errors[day][field] = serverErrors[key];
                }
            });
        },

        async submitForm() {
            this.errors = {};

            const isValid = this.validateForm();

            if (!isValid) {
                this.error('Please fix the highlighted errors before saving.');
                return;
            }

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
                    if (response.status === 422 && data.errors) {
                        this.mapBackendErrors(data.errors);
                        this.error('Please fix the highlighted errors before saving.');
                        return;
                    }

                    throw new Error(data.message || 'Error saving');
                }

                this.toast(data.message || 'Saved successfully');
            } catch (error) {
                this.error(error.message || 'Something went wrong.');
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
