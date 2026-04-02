document.addEventListener('alpine:init', () => {
    Alpine.data('tourManager', (config = {}) => {

        let editorInstance = null;
        let editorInitializing = false;

        return {
            tours: config.tours || [],
            storeUrl: config.storeUrl,
            updateUrl: config.updateUrl,
            deleteUrl: config.deleteUrl,
            csrfToken: config.csrfToken,

            newTour: {
                city: '',
                from: '',
                to: '',
                description: '',
                enabled: true
            },

            editingIndex: null,
            showModal: false,
            selectedTour: null,
            citySuggestions: [],
            submitting: false,

            searchQuery: '',
            statusFilter: 'all',
            dateFrom: '',
            dateTo: '',
            currentPage: 1,
            perPage: 10,

            init() {
                this.$nextTick(() => this.initEditor());
            },

            initEditor() {
                const el = this.$refs.descriptionEditor;
                if (!el || editorInstance || editorInitializing) return;
                editorInitializing = true;

                ClassicEditor.create(el).then(editor => {
                    editorInstance = editor;

                    editor.model.document.on('change:data', () => {
                        this.newTour.description = editor.getData();
                    });

                }).catch(console.error);
            },

            async searchCity() {
                if (!this.newTour.city || this.newTour.city.length < 2) return;

                try {
                    const res = await fetch(`/search-cities?q=${this.newTour.city}`);
                    this.citySuggestions = await res.json();
                } catch {
                    this.citySuggestions = [];
                }
            },

            selectCity(city) {
                this.newTour.city = city.name;
                this.citySuggestions = [];
            },

            async addOrUpdateTour() {
                if (!this.newTour.city || !this.newTour.from || !this.newTour.to) {
                    return this.error('Fill all fields');
                }

                if (new Date(this.newTour.to) <= new Date(this.newTour.from)) {
                    return this.error('"To" date must be after "From" date.');
                }

                this.submitting = true;

                let url = this.storeUrl;
                let method = 'POST';

                if (this.editingIndex !== null) {
                    const id = this.tours[this.editingIndex].id;
                    url = this.updateUrl.replace('__ID__', id);
                    method = 'PUT';
                }

                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify(this.newTour)
                    });

                    const data = await res.json();

                    if (!res.ok) throw new Error(data.message);

                    if (this.editingIndex !== null) {
                        this.tours[this.editingIndex] = data.tour;
                        this.toast('Updated');
                    } else {
                        this.tours.push(data.tour);
                        this.toast('Added');
                    }

                    this.resetForm();

                } catch (e) {
                    this.error(e.message);
                } finally {
                    this.submitting = false;
                }
            },

            editTour(tour) {
                this.editingIndex = this.tours.findIndex(t => t.id === tour.id);
                this.newTour = { ...tour };

                if (editorInstance) {
                    editorInstance.setData(tour.description || '');
                }
            },

            resetForm() {
                this.newTour = { city: '', from: '', to: '', description: '', enabled: true };
                this.editingIndex = null;

                if (editorInstance) editorInstance.setData('');
            },

            async confirmRemove(tour) {
                const result = await Swal.fire({
                    title: 'Delete?',
                    icon: 'warning',
                    showCancelButton: true
                });

                if (!result.isConfirmed) return;

                const id = tour.id;

                try {
                    const res = await fetch(this.deleteUrl.replace('__ID__', id), {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken }
                    });

                    if (!res.ok) throw new Error();

                    this.tours = this.tours.filter(t => t.id !== id);
                    this.toast('Deleted');

                } catch {
                    this.error('Delete failed');
                }
            },

            toggleStatus(tour) {
                tour.enabled = !tour.enabled;
            },

            toast(msg) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: msg,
                    timer: 1500,
                    showConfirmButton: false
                });
            },

            error(msg) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });
            }
        };
    });
});
