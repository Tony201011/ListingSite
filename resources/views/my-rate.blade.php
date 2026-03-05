@extends('layouts.frontend')

@section('content')
<!-- Main Content -->
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-5">

        <!-- Back button -->
        <button onclick="window.history.back()" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium bg-transparent border-0 cursor-pointer">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Go back
        </button>

        <!-- Page Title -->
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#e04ecb] pl-4">
                My <span class="text-[#e04ecb]">Rates</span>
            </h2>
        </div>

        <!-- Description card -->
        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-gray-100 mb-8">
            <p class="text-gray-700 text-lg leading-relaxed">
                You can group your rates by the type of services you offer, for example:
                <span class="block mt-2 text-gray-600 text-base">
                    massages, <span class="font-semibold text-[#e04ecb]">gfe</span>, <span class="font-semibold text-[#e04ecb]">pse</span>, kink/bdsm, netflix and chill, online services, lunch / dinner dates, extended & overnight dates, fmty, etc.
                </span>
            </p>
        </div>

        <!-- Alpine Component for Rates -->
        <div x-data="ratesManager()" x-init="init()" class="space-y-8">
            <!-- Your rates (not in a group) Section -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-gray-100">
                <div class="flex items-baseline flex-wrap gap-2 mb-2">
                    <h2 class="text-2xl font-semibold text-gray-800">Your rates</h2>
                    <span class="text-gray-500 text-sm">(not in a group)</span>
                </div>
                <p class="text-gray-600 mb-6">
                    If you don't have many rates or there is no need to put them in separate groups, you can list them here.
                </p>

                <!-- Rates Table -->
                <div class="overflow-x-auto mb-6">
                    <table class="w-full border-collapse border border-gray-300 rounded-lg">
                        <thead x-show="rates.length > 0" class="bg-gray-100">
                            <tr>
                                <th class="p-3 text-left font-semibold text-gray-700 border border-gray-300">Description</th>
                                <th class="p-3 text-left font-semibold text-gray-700 border border-gray-300">Incall</th>
                                <th class="p-3 text-left font-semibold text-gray-700 border border-gray-300">Outcall</th>
                            </tr>
                        </thead>
                        <tbody id="ratesList" class="divide-y divide-gray-200">
                            <template x-for="(rate, index) in rates" :key="index">
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-gray-800 font-medium border border-gray-300" x-text="rate.desc"></td>
                                    <td class="p-3 text-gray-800 font-medium border border-gray-300" x-text="rate.incall ? '$' + rate.incall : '—'"></td>
                                    <td class="p-3 text-gray-800 font-medium border border-gray-300" x-text="rate.outcall ? '$' + rate.outcall : '—'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <p x-show="rates.length === 0" class="text-gray-500 italic py-4 text-center border border-gray-200 rounded-lg mt-4">
                        No rates added yet. Click "Add rate" to create your first rate.
                    </p>
                </div>

                <!-- Add Rate Button -->
                <button x-show="!showForm" @click="showForm = true" class="bg-[#e04ecb] hover:bg-[#c13ab0] text-white font-medium px-8 py-3 rounded-full shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                    + Add rate
                </button>

                <!-- Add Rate Form -->
                <div x-show="showForm" x-transition class="mt-8">
                    <div class="bg-pink-50 border border-[#e04ecb] rounded-xl p-6">
                        <div class="border-b border-[#e04ecb]/30 pb-4 mb-6">
                            <span class="text-xl font-semibold text-gray-800">Add new rate</span>
                            <span class="text-gray-600 text-sm ml-3">If you don't have an incall or outcall rate, leave blank</span>
                        </div>

                        <form @submit.prevent="addRate" class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-1">
                                    <label class="block font-semibold text-[#e04ecb] mb-1">Description</label>
                                    <input type="text" x-model="newRate.desc" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent" placeholder="e.g. 1 hour GFE">
                                    <p class="text-[#e04ecb] text-xs mt-1">What do I type in here?</p>
                                </div>
                                <div>
                                    <label class="block font-semibold text-[#e04ecb] mb-1">Incall ($)</label>
                                    <input type="text" x-model="newRate.incall" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent" placeholder="250">
                                </div>
                                <div>
                                    <label class="block font-semibold text-[#e04ecb] mb-1">Outcall ($)</label>
                                    <input type="text" x-model="newRate.outcall" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent" placeholder="300">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-[#e04ecb] mb-1">Extra info <span class="text-gray-400 font-normal">(optional)</span></label>
                                <textarea x-model="newRate.extra" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent" placeholder="Additional details..."></textarea>
                                <p class="text-[#e04ecb] text-xs mt-1">What do I type in here?</p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="bg-[#e04ecb] hover:bg-[#c13ab0] text-white font-medium px-8 py-3 rounded-full shadow-md hover:shadow-lg transition">
                                    + Add rate
                                </button>
                                <button type="button" @click="showForm = false; resetForm()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-6 py-3 rounded-full transition">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Create a new group Section -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-gray-100">
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Create a new group</h2>
                <p class="text-gray-600 mb-6">If you like to create groups, use the button below to create a group for a specific service you offer.</p>
                <button class="bg-[#e04ecb] hover:bg-[#c13ab0] text-white font-medium px-8 py-3 rounded-full shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                    + Create a new rates group
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js and Font Awesome -->
<script src="//unpkg.com/alpinejs" defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    function ratesManager() {
        return {
            // State
            rates: [],                // Array of rate objects
            showForm: false,          // Toggle add form
            newRate: {
                desc: '',
                incall: '',
                outcall: '',
                extra: ''
            },

            // Methods
            init() {
                // Load any saved rates from localStorage or API if needed
                // For demo, we can start with some example data
                // this.rates = [
                //     { desc: '1 hour GFE', incall: '250', outcall: '300', extra: '' }
                // ];
            },

            addRate() {
                // Validate description (at least)
                const desc = this.newRate.desc.trim() || '—';
                const incall = this.newRate.incall.trim() || '';
                const outcall = this.newRate.outcall.trim() || '';

                this.rates.push({
                    desc: desc,
                    incall: incall,
                    outcall: outcall,
                    extra: this.newRate.extra.trim()
                });

                // Reset form and hide it
                this.resetForm();
                this.showForm = false;
            },

            resetForm() {
                this.newRate = { desc: '', incall: '', outcall: '', extra: '' };
            }
        }
    }
</script>
@endsection
