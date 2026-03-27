@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
    x-data="availabilityForm({
        initialForm: @js(collect($days)->mapWithKeys(fn ($day) => [
            $day => [
                'enabled' => (bool) old("availability.$day.enabled", $saved[$day]?->enabled ?? 1),
                'from' => old("availability.$day.from", $saved[$day]?->from_time ?? ''),
                'to' => old("availability.$day.to", $saved[$day]?->to_time ?? ''),
                'till_late' => (bool) old("availability.$day.till_late", $saved[$day]?->till_late ?? 0),
                'all_day' => (bool) old("availability.$day.all_day", $saved[$day]?->all_day ?? 0),
                'by_appointment' => (bool) old("availability.$day.by_appointment", $saved[$day]?->by_appointment ?? 0),
            ]
        ])),
        updateUrl: @js(route('availability.update')),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="max-w-4xl mx-auto">

        <button
            onclick="window.history.back()"
            class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
        >
            <span class="mr-1">&lt;</span> Go back
        </button>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Set your availability</h2>

                <p class="text-lg text-gray-600 mb-6 font-medium">
                    Set your weekly schedule so clients can easily see when you are available.
                </p>

                <div class="mb-8 rounded-xl bg-gray-50 border border-gray-100 p-5">
                    <ul class="list-disc pl-5 text-gray-700 space-y-2">
                        <li>This schedule repeats every week.</li>
                        <li>Uncheck days you do not work.</li>
                        <li>You can override for specific dates.</li>
                    </ul>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5">

                    @foreach($days as $day)
                        <div class="rounded-2xl border border-gray-200 p-5">
                            <label class="flex items-center gap-3 mb-3">
                                <input type="checkbox" x-model="form['{{ $day }}'].enabled">
                                <span class="font-semibold">{{ $day }}</span>
                            </label>

                            <div class="flex gap-4 mb-3">
                                <select x-model="form['{{ $day }}'].from">
                                    <option value="">From</option>
                                    @for($i=0;$i<=23;$i++)
                                        <option>{{ str_pad($i,2,'0',STR_PAD_LEFT) }}:00</option>
                                    @endfor
                                </select>

                                <select x-model="form['{{ $day }}'].to">
                                    <option value="">To</option>
                                    @for($i=0;$i<=23;$i++)
                                        <option>{{ str_pad($i,2,'0',STR_PAD_LEFT) }}:00</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="flex gap-4">
                                <label><input type="checkbox" x-model="form['{{ $day }}'].all_day" @change="handleAllDay('{{ $day }}')"> All day</label>
                                <label><input type="checkbox" x-model="form['{{ $day }}'].till_late"> Till late</label>
                                <label><input type="checkbox" x-model="form['{{ $day }}'].by_appointment"> Appointment</label>
                            </div>
                        </div>
                    @endforeach

                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-6 py-3 bg-pink-600 text-white rounded-lg"
                    >
                        <span x-show="!loading">Save</span>
                        <span x-show="loading">Saving...</span>
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('profile/js/availability-form.js') }}"></script>
@endpush
