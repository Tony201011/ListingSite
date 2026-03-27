document.addEventListener('alpine:init', () => {
    Alpine.data('editProfilePage', (config = {}) => ({
        ...config.initial,

        searchResults: [],
        showResults: false,
        searching: false,
        debounceTimer: null,

        submitting: false,
        errors: [],

        submitUrl: config.submitUrl,
        csrfToken: config.csrfToken,

        toggleTag(group, tag, event) {
            if (group === 'primaryIdentity') {
                this.primaryIdentity = [tag];
            } else if (group === 'attributes') {
                this.attributes.includes(tag)
                    ? this.attributes = this.attributes.filter(t => t !== tag)
                    : this.attributes.push(tag);
            } else if (group === 'servicesStyle') {
                if (this.servicesStyle.includes(tag)) {
                    this.servicesStyle = this.servicesStyle.filter(t => t !== tag);
                } else if (this.servicesStyle.length < 12) {
                    this.servicesStyle.push(tag);
                } else {
                    event.currentTarget.classList.add('shake');
                    setTimeout(() => event.currentTarget.classList.remove('shake'), 300);
                }
            }
        },

        toggleService(service) {
            this.services_provided.includes(service)
                ? this.services_provided = this.services_provided.filter(s => s !== service)
                : this.services_provided.push(service);
        },

        handleSuburbInput() {
            this.suburbSelected = false;
            this.searchSuburbs();
        },

        handleSuburbBlur() {
            setTimeout(() => this.showResults = false, 200);
        },

        searchSuburbs() {
            if (!this.suburb || this.suburb.length < 2) return;

            clearTimeout(this.debounceTimer);

            this.debounceTimer = setTimeout(async () => {
                this.searching = true;

                try {
                    const res = await fetch(`/api/suburbs/search?q=${encodeURIComponent(this.suburb)}`);
                    this.searchResults = await res.json();
                    this.showResults = true;
                } catch {
                    this.searchResults = [];
                } finally {
                    this.searching = false;
                }
            }, 300);
        },

        selectSuburb(item) {
            this.suburb = `${item.suburb}, ${item.state} ${item.postcode}`;
            this.suburbSelected = true;
            this.showResults = false;
        },

        validate() {
            let errors = [];

            if (!this.name) errors.push('Name is required');
            if (!this.mobile) errors.push('Mobile required');
            if (!this.suburbSelected) errors.push('Select suburb from dropdown');

            return errors;
        },

        async submitForm() {
            this.errors = this.validate();

            if (this.errors.length) {
                this.showError(this.errors);
                return;
            }

            this.submitting = true;

            const formData = new FormData(document.getElementById('editProfileForm'));

            try {
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();

                if (res.ok) {
                    this.showSuccess(data.message || 'Profile updated');
                } else {
                    this.showError(Object.values(data.errors || {}).flat());
                }

            } catch {
                this.showError(['Network error']);
            } finally {
                this.submitting = false;
            }
        },

        showSuccess(msg) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: msg,
                timer: 2000,
                showConfirmButton: false
            });
        },

        showError(errors) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `<ul style="text-align:left">${errors.map(e => `<li>${e}</li>`).join('')}</ul>`
            });
        }
    }));
});
