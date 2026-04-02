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

            get minDateTime() {
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');
                return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
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

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredTours.length / this.perPage));
            },

            get paginatedGroups() {
                const start = (this.currentPage - 1) * this.perPage;
                const paginated = this.filteredTours.slice(start, start + this.perPage);

                const groups = {};
                paginated.forEach(tour => {
                    const date = tour.from ? tour.from.substring(0, 10) : 'No date';
                    const heading = this.formatDateHeading(date);
                    if (!groups[heading]) groups[heading] = [];
                    groups[heading].push(tour);
                });

                return Object.keys(groups).map(heading => ({
                    heading,
                    tours: groups[heading]
                }));
            },

            formatDateHeading(dateStr) {
                if (dateStr === 'No date') return dateStr;
                try {
                    const d = new Date(dateStr + 'T00:00:00');
                    return d.toLocaleDateString('en-AU', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                } catch {
                    return dateStr;
                }
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
