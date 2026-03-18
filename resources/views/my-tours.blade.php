@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
     x-data="tourManager({{ $tours->toJson() }})"
     x-init="init()">
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">My tours</h1>
            <p class="text-gray-600 mb-6">Plan upcoming city tours so clients can pre-book in advance.</p>

            <!-- Input form -->
            <div class="grid sm:grid-cols-3 gap-3 relative">
                <!-- City with autocomplete API -->
                <div class="relative">
                    <input
                        x-model="newTour.city"
                        type="text"
                        placeholder="City"
                        @input.debounce.300ms="searchCity"
                        @focus="newTour.city.length >= 2 ? searchCity() : citySuggestions = []"
                        @click.away="citySuggestions = []"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent text-gray-900 bg-white"
                    >
                    <div
                        x-show="citySuggestions.length > 0"
                        x-cloak
                        class="absolute z-10 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-60 overflow-y-auto text-gray-900"
                    >
                        <template x-for="(city, idx) in citySuggestions" :key="idx">
                            <div @click="selectCity(city)" class="px-4 py-2 hover:bg-pink-50 cursor-pointer">
                                <span x-text="city.name"></span>
                                <span x-text="city.adminName1 ? `, ${city.adminName1}` : ''" class="text-gray-500 text-sm"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- From datetime -->
                <input
                    x-model="newTour.from"
                    type="datetime-local"
                    :min="editingIndex === null ? minDateTime : null"
                    class="px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent text-gray-900 bg-white"
                >

                <!-- To datetime -->
                <input
                    x-model="newTour.to"
                    type="datetime-local"
                    :min="editingIndex === null ? minDateTime : null"
                    class="px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent text-gray-900 bg-white"
                >
            </div>

            <div class="mt-3">
                <textarea
                    x-ref="descriptionEditor"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent text-gray-900 bg-white"
                    placeholder="Tour description"
                    rows="4"
                ></textarea>
            </div>

            <!-- Enabled toggle -->
            <div class="mt-3 flex items-center">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="newTour.enabled" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700" x-text="newTour.enabled ? 'Enabled' : 'Disabled'"></span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 flex flex-col sm:flex-row items-center gap-3">
                <button
                    @click="addOrUpdateTour"
                    :disabled="submitting"
                    class="w-full md:w-auto px-10 py-4 bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-lg rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition focus:outline-none focus:ring-2 focus:ring-[#e04ecb] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!submitting">
                        <span x-show="editingIndex === null">Add tour</span>
                        <span x-show="editingIndex !== null">Update tour</span>
                    </span>
                    <span x-show="submitting">Saving...</span>
                </button>

                <button
                    @click="cancelEdit"
                    x-show="editingIndex !== null"
                    class="w-full md:w-auto px-6 py-3 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-semibold transition"
                >
                    Cancel
                </button>
            </div>

            <!-- Tours list with grouping, date filters, and pagination -->
            <div class="mt-8" x-show="tours.length > 0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Your scheduled tours</h2>

                    <!-- Search, filter, and date range controls -->
                    <div class="flex flex-col gap-2 w-full sm:w-auto">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input
                                type="text"
                                x-model="searchQuery"
                                @input="currentPage = 1"
                                placeholder="Search by city or description..."
                                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                            <select
                                x-model="statusFilter"
                                @change="currentPage = 1"
                                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent bg-white"
                            >
                                <option value="all">All status</option>
                                <option value="enabled">Enabled only</option>
                                <option value="disabled">Disabled only</option>
                            </select>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input
                                type="date"
                                x-model="dateFrom"
                                @change="currentPage = 1"
                                placeholder="From date"
                                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                            <input
                                type="date"
                                x-model="dateTo"
                                @change="currentPage = 1"
                                placeholder="To date"
                                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                        </div>
                    </div>
                </div>

                <!-- Grouped tours (from paginated results) -->
                <template x-for="group in paginatedGroups" :key="group.heading">
                    <div x-show="group.tours.length > 0" class="mb-6">
                        <h3 class="text-md font-semibold text-gray-700 mb-2" x-text="group.heading"></h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left border border-gray-200">
                                <thead class="bg-gray-50 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 border-b">City</th>
                                        <th class="px-4 py-3 border-b">From</th>
                                        <th class="px-4 py-3 border-b">To</th>
                                        <th class="px-4 py-3 border-b">Description</th>
                                        <th class="px-4 py-3 border-b">Status</th>
                                        <th class="px-4 py-3 border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="tour in group.tours" :key="tour.id">
                                        <tr class="border-b hover:bg-gray-50 text-gray-900">
                                            <td class="px-4 py-3" x-text="tour.city"></td>
                                            <td class="px-4 py-3" x-text="formatDateTime(tour.from)"></td>
                                            <td class="px-4 py-3" x-text="formatDateTime(tour.to)"></td>
                                            <td class="px-4 py-3" x-text="plainDescription(tour.description)"></td>
                                            <td class="px-4 py-3">
                                                <button
                                                    @click="toggleStatus(tour)"
                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                                                    :class="tour.enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                >
                                                    <span x-text="tour.enabled ? 'Enabled' : 'Disabled'"></span>
                                                </button>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <button @click="openTourModal(tour)" class="text-gray-600 hover:text-gray-900 text-sm flex items-center gap-1" title="View details">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        <span class="sr-only">View</span>
                                                    </button>

                                                    <button @click="editTour(tour)" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                                                    <button @click="confirmRemove(tour)" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- Message when no tours match filters -->
                <div x-show="filteredTours.length === 0" class="text-center py-6 text-gray-500">
                    No tours match your filters.
                </div>

                <!-- Pagination controls -->
                <div x-show="filteredTours.length > perPage" class="flex justify-center items-center gap-4 mt-6">
                    <button
                        @click="currentPage = Math.max(1, currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <span class="text-sm text-gray-700">
                        Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                    </span>
                    <button
                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tour Details Modal (unchanged) -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tour Details</h3>

                            <div class="mt-4 space-y-3" x-show="selectedTour">
                                <div class="border-b pb-2">
                                    <span class="text-sm font-medium text-gray-500">City:</span>
                                    <p class="text-gray-900" x-text="selectedTour?.city"></p>
                                </div>

                                <div class="border-b pb-2">
                                    <span class="text-sm font-medium text-gray-500">From:</span>
                                    <p class="text-gray-900" x-text="formatDateTime(selectedTour?.from)"></p>
                                </div>

                                <div class="border-b pb-2">
                                    <span class="text-sm font-medium text-gray-500">To:</span>
                                    <p class="text-gray-900" x-text="formatDateTime(selectedTour?.to)"></p>
                                </div>

                                <div class="border-b pb-2">
                                    <span class="text-sm font-medium text-gray-500">Description:</span>
                                    <div class="text-gray-900 prose prose-sm max-w-none" x-html="selectedTour?.description || ''"></div>
                                </div>

                                <div class="border-b pb-2">
                                    <span class="text-sm font-medium text-gray-500">Status:</span>
                                    <p class="text-gray-900" x-text="selectedTour?.enabled ? 'Enabled' : 'Disabled'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .ck-editor__editable {
        min-height: 150px !important;
        font-size: 1rem !important;
        color: #1f2937 !important;
        background-color: #ffffff !important;
        border-radius: 0.5rem !important;
    }
    .ck.ck-editor {
        width: 100% !important;
    }
    [x-cloak] {
        display: none !important;
    }
    input, textarea, td, th {
        color: #111827 !important;
    }
    .text-gray-500, .text-gray-600, .text-gray-700 {
        color: #4b5563 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
function tourManager(initialTours = []) {
    // Keep CKEditor instance outside the reactive object
    let editorInstance = null;

    return {
        newTour: {
            city: '',
            from: '',
            to: '',
            description: '',
            enabled: true
        },
        tours: initialTours,
        editingIndex: null,
        showModal: false,
        selectedTour: null,
        citySuggestions: [],

        // Filtering properties
        searchQuery: '',
        statusFilter: 'all',
        dateFrom: '',
        dateTo: '',

        // Pagination
        currentPage: 1,
        perPage: 10,

        // Submitting state for button
        submitting: false,

        get minDateTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        },

        get filteredTours() {
            let filtered = this.tours;

            if (this.statusFilter === 'enabled') {
                filtered = filtered.filter(tour => tour.enabled);
            } else if (this.statusFilter === 'disabled') {
                filtered = filtered.filter(tour => !tour.enabled);
            }

            if (this.searchQuery.trim() !== '') {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(tour =>
                    (tour.city || '').toLowerCase().includes(query) ||
                    (tour.description || '').toLowerCase().includes(query)
                );
            }

            if (this.dateFrom) {
                const fromDate = new Date(this.dateFrom);
                filtered = filtered.filter(tour => new Date(tour.from) >= fromDate);
            }

            if (this.dateTo) {
                const toDate = new Date(this.dateTo);
                toDate.setDate(toDate.getDate() + 1);
                filtered = filtered.filter(tour => new Date(tour.to) <= toDate);
            }

            return filtered;
        },

        get totalPages() {
            return Math.ceil(this.filteredTours.length / this.perPage) || 1;
        },

        get paginatedTours() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredTours.slice(start, start + this.perPage);
        },

        getTourCategory(tour) {
            const now = new Date();
            const from = new Date(tour.from);
            const to = new Date(tour.to);

            if (now < from) return 'upcoming';
            if (now > to) return 'past';
            return 'ongoing';
        },

        get paginatedGroups() {
            const groups = {
                upcoming: [],
                ongoing: [],
                past: []
            };

            this.paginatedTours.forEach(tour => {
                const category = this.getTourCategory(tour);
                groups[category].push(tour);
            });

            return [
                { heading: 'Upcoming', tours: groups.upcoming },
                { heading: 'Ongoing', tours: groups.ongoing },
                { heading: 'Past', tours: groups.past }
            ];
        },

        toDatetimeLocal(datetimeString) {
            if (!datetimeString) return '';
            const date = new Date(datetimeString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        },

        init() {
            this.$nextTick(() => {
                this.initEditor();
            });
        },

        initEditor() {
            const element = this.$refs.descriptionEditor;
            if (!element || editorInstance) return;
            if (element.dataset.ckInitialized === 'true') return;

            element.dataset.ckInitialized = 'true';

            ClassicEditor.create(element)
                .then(editor => {
                    editorInstance = editor;

                    // Sync changes back to model
                    editor.model.document.on('change:data', () => {
                        this.newTour.description = editor.getData();
                    });

                    // If a description was already set (e.g., from editTour before editor loaded), load it now
                    if (this.newTour.description) {
                        editor.setData(this.newTour.description);
                    }
                })
                .catch(error => {
                    console.error('CKEditor error:', error);
                    element.dataset.ckInitialized = 'false';
                });
        },

        async searchCity() {
            const query = this.newTour.city.trim();
            if (!query || query.length < 2) {
                this.citySuggestions = [];
                return;
            }

            try {
                const response = await fetch(`/search-cities?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                this.citySuggestions = data;
            } catch (error) {
                console.error('City search error:', error);
                this.citySuggestions = [];
            }
        },

        selectCity(city) {
            this.newTour.city = city.adminName1 ? `${city.name}, ${city.adminName1}` : city.name;
            this.citySuggestions = [];
        },

        formatDateTime(datetimeString) {
            if (!datetimeString) return '';
            const date = new Date(datetimeString);
            return date.toLocaleString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        plainDescription(html) {
            if (!html) return '';

            const div = document.createElement('div');
            div.innerHTML = html;
            const text = (div.textContent || div.innerText || '').trim();

            return text.length > 60 ? text.slice(0, 60) + '…' : text;
        },

        openTourModal(tour) {
            this.selectedTour = tour;
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.selectedTour = null;
        },

        async addOrUpdateTour() {
            if (!this.newTour.city || !this.newTour.from || !this.newTour.to) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Missing fields',
                    text: 'Please fill all fields (city, from, to)',
                    timer: 3000,
                    showConfirmButton: true
                });
                return;
            }

            const now = new Date();
            const fromDate = new Date(this.newTour.from);
            const toDate = new Date(this.newTour.to);

            if (this.editingIndex === null) {
                if (fromDate < now) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Invalid date',
                        text: 'From date cannot be in the past',
                        timer: 3000,
                        showConfirmButton: true
                    });
                    return;
                }

                if (toDate < now) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Invalid date',
                        text: 'To date cannot be in the past',
                        timer: 3000,
                        showConfirmButton: true
                    });
                    return;
                }
            }

            if (toDate < fromDate) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Invalid date range',
                    text: 'To date must be after From date',
                    timer: 3000,
                    showConfirmButton: true
                });
                return;
            }

            this.submitting = true;

            const url = this.editingIndex !== null
                ? `{{ route('my-tours.update', '') }}/${this.tours[this.editingIndex].id}`
                : '{{ route('my-tours.store') }}';

            const method = this.editingIndex !== null ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.newTour),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                if (this.editingIndex !== null) {
                    this.tours[this.editingIndex] = data.tour;
                    this.cancelEdit();

                    await Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    this.tours.push(data.tour);
                    this.resetForm();
                    this.currentPage = 1;

                    await Swal.fire({
                        icon: 'success',
                        title: 'Added!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }

                if (editorInstance) {
                    editorInstance.setData('');
                }
            } catch (error) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                });
            } finally {
                this.submitting = false;
            }
        },

        editTour(tour) {
            const index = this.tours.findIndex(t => t.id === tour.id);
            if (index === -1) return;

            this.editingIndex = index;
            this.newTour = {
                city: tour.city || '',
                from: this.toDatetimeLocal(tour.from),
                to: this.toDatetimeLocal(tour.to),
                description: tour.description || '',
                enabled: !!tour.enabled
            };

            this.citySuggestions = [];

            // Set editor content if editor is ready; otherwise, it will be set when editor initializes
            if (editorInstance) {
                editorInstance.setData(tour.description || '');
            }
        },

        cancelEdit() {
            this.editingIndex = null;
            this.resetForm();
        },

        resetForm() {
            this.newTour = {
                city: '',
                from: '',
                to: '',
                description: '',
                enabled: true
            };

            this.citySuggestions = [];

            if (editorInstance) {
                editorInstance.setData('');
            }
        },

        async confirmRemove(tour) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                const tourId = tour.id;
                const index = this.tours.findIndex(t => t.id === tourId);

                try {
                    const response = await fetch(`{{ route('my-tours.destroy', '') }}/${tourId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Something went wrong.');
                    }

                    this.tours.splice(index, 1);

                    if (this.editingIndex === index) {
                        this.cancelEdit();
                    }

                    if (this.filteredTours.length > 0 && this.paginatedTours.length === 0) {
                        this.currentPage = Math.max(1, this.currentPage - 1);
                    }

                    await Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } catch (error) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message,
                    });
                }
            }
        },

        toggleStatus(tour) {
            const index = this.tours.findIndex(t => t.id === tour.id);
            if (index !== -1) {
                this.tours[index].enabled = !this.tours[index].enabled;
            }
        }
    }
}
</script>
@endpush
@endsection
