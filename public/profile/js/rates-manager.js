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

        openFormForAdd() {
            this.resetForm();
            this.showForm = true;
        },

        editRate(rate) {
            this.form = { ...rate };
            this.editingId = rate.id;
            this.showForm = true;
            this.validationErrors = {};
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

        async saveRate() {
            if (!this.validateForm()) return;

            this.isSubmitting = true;

            const payload = {
                description: this.form.desc,
                incall: this.form.incall,
                outcall: this.form.outcall,
                extra: this.form.extra
            };

            let url = this.storeUrl;
            let method = 'POST';

            if (this.editingId) {
                url = this.updateUrl.replace('__ID__', this.editingId);
                method = 'PUT';
            }

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
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

                if (this.editingId) {
                    const index = this.rates.findIndex(r => r.id === this.editingId);
                    this.rates[index] = {
                        id: data.id,
                        desc: data.description,
                        incall: data.incall,
                        outcall: data.outcall,
                        extra: data.extra
                    };
                    this.toast('Updated successfully');
                } else {
                    this.rates.push({
                        id: data.id,
                        desc: data.description,
                        incall: data.incall,
                        outcall: data.outcall,
                        extra: data.extra
                    });
                    this.toast('Added successfully');
                }

                this.cancelForm();

            } catch (e) {
                this.error('Something went wrong');
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

                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                if (!res.ok) throw new Error();

                this.rates.splice(index, 1);
                this.toast('Deleted successfully');

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
