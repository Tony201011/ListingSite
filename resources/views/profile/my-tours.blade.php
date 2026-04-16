@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
     x-data="tourManager({
            tours: @js($tours),
            storeUrl: @js(route('my-tours.store')),
            updateUrl: @js(route('my-tours.update', ['tour' => '__ID__'])),
            deleteUrl: @js(route('my-tours.destroy', ['tour' => '__ID__'])),
            csrfToken: @js(csrf_token())
        })"
     x-init="init()">
    <div class="max-w-4xl mx-auto">
        @include('profile.partials.back-to-settings')

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
                    :min="newTour.from || (editingIndex === null ? minDateTime : null)"
                    class="px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent text-gray-900 bg-white"
                >
            </div>

            <div class="mt-3">
                <div
                    x-ref="descriptionEditor"
                    class="w-full bg-white"
                ></div>
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

            <!-- Tours list with category tabs -->
            <div class="mt-8" x-show="tours.length > 0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Your scheduled tours</h2>

                    <!-- Search and filter controls -->
                    <div class="flex flex-col gap-2 w-full sm:w-auto">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input
                                type="text"
                                x-model="searchQuery"
                                placeholder="Search by city or description..."
                                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                            <select
                                x-model="statusFilter"
                                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent bg-white"
                            >
                                <option value="all">All status</option>
                                <option value="enabled">Enabled only</option>
                                <option value="disabled">Disabled only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="flex border-b border-gray-200 mb-4">
                    <button
                        @click="categoryTab = 'upcoming'"
                        :class="categoryTab === 'upcoming' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-4 py-2 text-sm font-medium border-b-2 transition whitespace-nowrap"
                    >
                        Upcoming <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs" :class="categoryTab === 'upcoming' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-600'" x-text="upcomingTours.length"></span>
                    </button>
                    <button
                        @click="categoryTab = 'ongoing'"
                        :class="categoryTab === 'ongoing' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-4 py-2 text-sm font-medium border-b-2 transition whitespace-nowrap"
                    >
                        Ongoing <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs" :class="categoryTab === 'ongoing' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" x-text="ongoingTours.length"></span>
                    </button>
                    <button
                        @click="categoryTab = 'past'"
                        :class="categoryTab === 'past' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-4 py-2 text-sm font-medium border-b-2 transition whitespace-nowrap"
                    >
                        Past <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs" :class="categoryTab === 'past' ? 'bg-gray-200 text-gray-700' : 'bg-gray-100 text-gray-600'" x-text="pastTours.length"></span>
                    </button>
                </div>

                <!-- Tour table for active category -->
                <div x-show="activeCategoryTours.length > 0">
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
                                <template x-for="tour in activeCategoryTours" :key="tour.id">
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

                                                <button @click="editTour(tour)" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1" title="Edit tour">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                    <span class="sr-only">Edit</span>
                                                </button>
                                                <button @click="confirmRemove(tour)" class="text-red-600 hover:text-red-800 text-sm flex items-center gap-1" title="Remove tour">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span class="sr-only">Remove</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty state for active category -->
                <div x-show="activeCategoryTours.length === 0" class="text-center py-8 text-gray-500">
                    <span x-show="categoryTab === 'upcoming'">No upcoming tours.</span>
                    <span x-show="categoryTab === 'ongoing'">No ongoing tours.</span>
                    <span x-show="categoryTab === 'past'">No past tours.</span>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<link rel="stylesheet" href="{{ asset('css/quill-editor.css') }}">
<style>
    .ql-editor {
        min-height: 150px !important;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }
    .ql-toolbar.ql-snow {
        border-color: #e5e7eb !important;
    }
    .ql-container.ql-snow {
        border-color: #e5e7eb !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="{{ asset('profile/js/tour-manager.js') }}?v={{ filemtime(public_path('profile/js/tour-manager.js')) }}"></script>
@endpush
@endsection
