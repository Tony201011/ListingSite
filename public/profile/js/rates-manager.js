document.addEventListener('alpine:init', () => {
    Alpine.data('ratesManager', (config = {}) => ({
        rates: config.rates || [],
        storeUrl: config.storeUrl,
        updateUrl: config.updateUrl,
        deleteUrl: config.deleteUrl,
        csrfToken: config.csrfToken,

        showForm: false,
        isSubmitting: false,
        editingId: null,

        form: {
            desc: '',
            incall: '',
            outcall: '',
            extra: ''
        },

        validationErrors: {},

        scrollTo(selector, offset = 80) {
            this.$nextTick(() => {
                const candidates = selector.split(',').map(s => document.querySelector(s.trim())).filter(Boolean);
                const el = candidates.find(e => e.offsetParent !== null) || candidates[0];
                if (!el) return;
                const y = el.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: y, behavior: 'smooth' });
            });
        },

        openFormForAdd() {
            this.resetForm();
            this.showForm = true;
            this.scrollTo('#rate-form');
        },

        editRate(rate) {
            this.form = { ...rate };
            this.editingId = rate.id;
            this.showForm = true;
            this.validationErrors = {};
            this.scrollTo('#rate-form');
        },

        cancelForm() {
            this.resetForm();
            this.showForm = false;
        },

        validateForm() {
            this.validationErrors = {};

            if (!this.form.desc) {
                this.validationErrors.desc = 'Description required';
            }

            if (!this.form.incall) {
                this.validationErrors.incall = 'Incall required';
            }

            if (!this.form.outcall) {
                this.validationErrors.outcall = 'Outcall required';
            }

            return Object.keys(this.validationErrors).length === 0;
        },

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || this.csrfToken || '';
        },

        getXsrfTokenFromCookie() {
            const cookie = document.cookie
                .split(';')
                .map(row => row.trim())
                .find(row => row.startsWith('XSRF-TOKEN='));

            return cookie ? decodeURIComponent(cookie.split('=').slice(1).join('=')) : '';
        },

        csrfHeaders() {
            const xsrfToken = this.getXsrfTokenFromCookie();
            if (xsrfToken) {
                return { 'X-XSRF-TOKEN': xsrfToken };
            }

            const csrfToken = this.getCsrfToken();
            if (csrfToken) {
                return { 'X-CSRF-TOKEN': csrfToken };
            }

            return {};
        },

        requireCsrfHeaders() {
            const headers = this.csrfHeaders();
            if (Object.keys(headers).length > 0) {
                return headers;
            }

            throw new Error('CSRF/XSRF security token not found. Please refresh the page and sign in again if the issue continues.');
        },

        async saveRate() {
            if (!this.validateForm()) return;

            this.isSubmitting = true;

            const payload = {
                description: this.form.desc,
                incall: this.form.incall,
                outcall: this.form.outcall,
                extra: this.form.extra,
            };

            let url = this.storeUrl;
            let method = 'POST';

            if (this.editingId) {
                url = this.updateUrl.replace('__ID__', this.editingId);
                method = 'PUT';
            }

            try {
                const csrfHeaders = this.requireCsrfHeaders();
                const res = await fetch(url, {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...csrfHeaders,
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok) {
                    if (res.status === 422) {
                        this.validationErrors = data.errors || {};
                        return;
                    }
                    throw new Error(data.message);
                }

                const rate = data.rate || data;

                const mapped = {
                    id: rate.id,
                    desc: rate.description || rate.desc || '',
                    incall: rate.incall || '',
                    outcall: rate.outcall || '',
                    extra: rate.extra || ''
                };

                if (this.editingId) {
                    const index = this.rates.findIndex(r => r.id === this.editingId);
                    if (index !== -1) {
                        this.rates.splice(index, 1, mapped);
                    }
                    this.toast('Updated successfully');
                    this.cancelForm();
                } else {
                    this.rates.push(mapped);
                    this.toast('Added successfully');
                    this.cancelForm();
                }

            } catch (e) {
                this.error(e.message || 'Something went wrong');
            } finally {
                this.isSubmitting = false;
            }
        },

        confirmDelete(id, index) {
            Swal.fire({
                title: 'Delete this rate?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e04ecb',
            }).then(result => {
                if (result.isConfirmed) {
                    this.deleteRate(id, index);
                }
            });
        },

        async deleteRate(id, index) {
            try {
                const url = this.deleteUrl.replace('__ID__', id);
                const csrfHeaders = this.requireCsrfHeaders();

                const res = await fetch(url, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...csrfHeaders,
                    }
                });

                if (!res.ok) throw new Error();

                this.rates.splice(index, 1);
                this.toast('Deleted successfully');
                this.scrollTo('#rates-container');

            } catch {
                this.error('Delete failed');
            }
        },

        resetForm() {
            this.form = { desc: '', incall: '', outcall: '', extra: '' };
            this.editingId = null;
            this.validationErrors = {};
        },

        toast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 1500
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
