document.addEventListener('alpine:init', () => {
    Alpine.data('tourManager', (config = {}) => {

        let editorInstance = null;

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
            categoryTab: 'upcoming',

            get minDateTime() {
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');
                return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
            },

            getTourCategory(tour) {
                const now = new Date();
                const from = new Date(tour.from);
                const to = new Date(tour.to);
                if (to < now) return 'past';
                if (from <= now && to >= now) return 'ongoing';
                return 'upcoming';
            },

            get filteredTours() {
                return this.tours.filter(tour => {
                    if (this.statusFilter === 'enabled' && !tour.enabled) return false;
                    if (this.statusFilter === 'disabled' && tour.enabled) return false;

                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        const cityMatch = (tour.city || '').toLowerCase().includes(q);
                        const descMatch = (tour.description || '').toLowerCase().includes(q);
                        if (!cityMatch && !descMatch) return false;
                    }

                    if (this.dateFrom && tour.from < this.dateFrom) return false;
                    if (this.dateTo && tour.to > this.dateTo + 'T23:59:59') return false;

                    return true;
                });
            },

            get upcomingTours() {
                return this.filteredTours.filter(t => this.getTourCategory(t) === 'upcoming');
            },

            get ongoingTours() {
                return this.filteredTours.filter(t => this.getTourCategory(t) === 'ongoing');
            },

            get pastTours() {
                return this.filteredTours.filter(t => this.getTourCategory(t) === 'past');
            },

            get activeCategoryTours() {
                if (this.categoryTab === 'ongoing') return this.ongoingTours;
                if (this.categoryTab === 'past') return this.pastTours;
                return this.upcomingTours;
            },

            formatDateTime(dt) {
                if (!dt) return '';
                try {
                    const d = new Date(dt);
                    return d.toLocaleString('en-AU', { dateStyle: 'medium', timeStyle: 'short' });
                } catch {
                    return dt;
                }
            },

            plainDescription(desc) {
                if (!desc) return '';
                const tmp = document.createElement('div');
                tmp.innerHTML = desc;
                const text = tmp.textContent || tmp.innerText || '';
                return text.length > 80 ? text.substring(0, 80) + '...' : text;
            },

            openTourModal(tour) {
                this.selectedTour = tour;
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
                this.selectedTour = null;
            },

            init() {
                this.$nextTick(() => this.initEditor());
            },

            initEditor() {
                const el = this.$refs.descriptionEditor;
                if (!el || editorInstance) return;

                editorInstance = new Quill(el, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link', 'blockquote'],
                            ['clean']
                        ]
                    },
                    placeholder: 'Tour description'
                });

                editorInstance.on('text-change', () => {
                    this.newTour.description = editorInstance.root.innerHTML;
                });
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
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
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
                    editorInstance.setContents([]);
                    editorInstance.clipboard.dangerouslyPasteHTML(0, tour.description || '');
                }
            },

            resetForm() {
                this.newTour = { city: '', from: '', to: '', description: '', enabled: true };
                this.editingIndex = null;

                if (editorInstance) {
                    editorInstance.setContents([]);
                }
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
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
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
