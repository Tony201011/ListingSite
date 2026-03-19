@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
    x-data="availabilityForm()"
    x-init="init()"
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

                <a
                    href="{{ route('availability.show') }}"
                    class="inline-block text-[#e04ecb] hover:text-[#c13ab0] underline text-sm font-medium mb-6"
                >
                    &lt;&lt;&lt; Show me my availability
                </a>

                <div class="mb-8 rounded-xl bg-gray-50 border border-gray-100 p-5">
                    <ul class="list-disc pl-5 text-sm sm:text-base text-gray-700 space-y-2">
                        <li>
                            This 7 day schedule will
                            <a href="{{ route('availability.show') }}" class="text-[#e04ecb] underline hover:text-[#c13ab0]">
                                repeat every week
                            </a>.
                        </li>
                        <li>Uncheck the days you do not work and for the days you work set times and availability.</li>
                        <li>You can always overrule your schedule for specific dates.</li>
                    </ul>
                </div>

                <div
                    x-show="successMessage"
                    x-transition
                    class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700"
                    x-text="successMessage"
                ></div>

                <div
                    x-show="errorMessage"
                    x-transition
                    class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700"
                    x-text="errorMessage"
                ></div>

                <form @submit.prevent="submitForm" class="space-y-5">
                    @csrf

                    @foreach($days as $day)
                        @php
                            $item = $saved[$day] ?? null;
                        @endphp

                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                                <div class="min-w-[140px]">
                                    <label class="inline-flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            x-model="form['{{ $day }}'].enabled"
                                            class="h-5 w-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                        >
                                        <span class="text-lg font-semibold text-gray-900">{{ $day }}</span>
                                    </label>
                                </div>

                                <div class="flex-1 space-y-4">
                                    <div class="flex flex-col sm:flex-row gap-4">
                                        <div class="w-full sm:w-48">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                                            <select
                                                x-model="form['{{ $day }}'].from"
                                                :disabled="!form['{{ $day }}'].enabled || form['{{ $day }}'].all_day"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:border-pink-500 focus:ring-pink-500 disabled:bg-gray-100 disabled:text-gray-400"
                                            >
                                                <option value="">FROM</option>
                                                @for($i = 0; $i <= 23; $i++)
                                                    @php $time = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                                                    <option value="{{ $time }}">{{ $time }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <div class="w-full sm:w-48">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Till</label>
                                            <select
                                                x-model="form['{{ $day }}'].to"
                                                :disabled="!form['{{ $day }}'].enabled || form['{{ $day }}'].all_day"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:border-pink-500 focus:ring-pink-500 disabled:bg-gray-100 disabled:text-gray-400"
                                            >
                                                <option value="">TILL</option>
                                                @for($i = 0; $i <= 23; $i++)
                                                    @php $time = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                                                    <option value="{{ $time }}">{{ $time }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-4">
                                        <label class="inline-flex items-center gap-2 text-gray-700">
                                            <input
                                                type="checkbox"
                                                x-model="form['{{ $day }}'].till_late"
                                                :disabled="!form['{{ $day }}'].enabled"
                                                class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                            >
                                            <span>Till late</span>
                                        </label>

                                        <label class="inline-flex items-center gap-2 text-gray-700">
                                            <input
                                                type="checkbox"
                                                x-model="form['{{ $day }}'].all_day"
                                                :disabled="!form['{{ $day }}'].enabled"
                                                @change="handleAllDay('{{ $day }}')"
                                                class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                            >
                                            <span>All day</span>
                                        </label>

                                        <label class="inline-flex items-center gap-2 text-gray-700">
                                            <input
                                                type="checkbox"
                                                x-model="form['{{ $day }}'].by_appointment"
                                                :disabled="!form['{{ $day }}'].enabled"
                                                class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                            >
                                            <span>By appointment</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-4 flex justify-center">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3.5 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 shadow-lg shadow-pink-600/30 transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span x-show="!loading">Update your availability</span>
                            <span x-show="loading">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function availabilityForm() {
        return {
            loading: false,
            successMessage: '',
            errorMessage: '',
            form: {
                @foreach($days as $day)
                    '{{ $day }}': {
                        enabled: {{ old("availability.$day.enabled", $saved[$day]?->enabled ?? 1) ? 'true' : 'false' }},
                        from: @json(old("availability.$day.from", $saved[$day]?->from_time ?? '')),
                        to: @json(old("availability.$day.to", $saved[$day]?->to_time ?? '')),
                        till_late: {{ old("availability.$day.till_late", $saved[$day]?->till_late ?? 0) ? 'true' : 'false' }},
                        all_day: {{ old("availability.$day.all_day", $saved[$day]?->all_day ?? 0) ? 'true' : 'false' }},
                        by_appointment: {{ old("availability.$day.by_appointment", $saved[$day]?->by_appointment ?? 0) ? 'true' : 'false' }},
                    },
                @endforeach
            },

            init() {
                Object.keys(this.form).forEach(day => {
                    if (this.form[day].all_day) {
                        this.form[day].from = '';
                        this.form[day].to = '';
                    }
                });
            },

            handleAllDay(day) {
                if (this.form[day].all_day) {
                    this.form[day].from = '';
                    this.form[day].to = '';
                }
            },

            buildPayload() {
                const availability = {};

                Object.keys(this.form).forEach(day => {
                    availability[day] = {
                        enabled: this.form[day].enabled ? 1 : 0,
                        from: this.form[day].from,
                        to: this.form[day].to,
                        till_late: this.form[day].till_late ? 1 : 0,
                        all_day: this.form[day].all_day ? 1 : 0,
                        by_appointment: this.form[day].by_appointment ? 1 : 0,
                    };
                });

                return { availability };
            },

            async submitForm() {
                this.loading = true;
                this.successMessage = '';
                this.errorMessage = '';

                try {
                    const response = await fetch('{{ route('availability.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.buildPayload())
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Something went wrong while saving availability.');
                    }

                    this.successMessage = data.message || 'Availability updated successfully.';
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to save availability.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endsection
