@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 sm:py-12 px-4 sm:px-6 lg:px-8"
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
    <div class="max-w-5xl mx-auto">
        <button
            type="button"
            onclick="window.history.back()"
            class="inline-flex items-center gap-2 text-sm font-medium text-pink-600 hover:text-pink-700 transition mb-6"
        >
            <span>&larr;</span>
            <span>Go back</span>
        </button>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 sm:p-8 lg:p-10">
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        Set your availability
                    </h2>
                    <p class="mt-3 text-base sm:text-lg text-gray-600 leading-7">
                        Set your weekly schedule so clients can easily see when you are available.
                    </p>
                </div>

                <div class="mb-8 rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base text-gray-700">
                        <li>This schedule repeats every week.</li>
                        <li>Uncheck days you do not work.</li>
                        <li>You can override availability for specific dates later.</li>
                    </ul>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5">
                    @foreach($days as $day)
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                <div class="lg:w-48">
                                    <label class="inline-flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            x-model="form['{{ $day }}'].enabled"
                                            class="h-5 w-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                        >
                                        <span class="text-base sm:text-lg font-semibold text-gray-900">
                                            {{ $day }}
                                        </span>
                                    </label>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Toggle this day on or off.
                                    </p>
                                </div>

                                <div class="flex-1 space-y-4">
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                        :class="{ 'opacity-50 pointer-events-none': !form['{{ $day }}'].enabled }"
                                    >
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                From
                                            </label>
                                            <select
                                                x-model="form['{{ $day }}'].from"
                                                :disabled="!form['{{ $day }}'].enabled || form['{{ $day }}'].all_day || form['{{ $day }}'].by_appointment"
                                                :class="getFieldError('{{ $day }}', 'from') ? 'border-red-500 ring-2 ring-red-100' : 'border-gray-300'"
                                                class="w-full rounded-xl bg-white px-4 py-3 text-base text-gray-900 shadow-sm focus:border-pink-500 focus:ring-2 focus:ring-pink-200 disabled:bg-gray-100 disabled:text-gray-400"
                                            >
                                                <option value="">Select start time</option>
                                                @for($i = 0; $i <= 23; $i++)
                                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00">
                                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00
                                                    </option>
                                                @endfor
                                            </select>

                                            <p
                                                x-show="getFieldError('{{ $day }}', 'from')"
                                                x-text="getFieldError('{{ $day }}', 'from')"
                                                class="mt-2 text-sm text-red-600"
                                            ></p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                To
                                            </label>
                                            <select
                                                x-model="form['{{ $day }}'].to"
                                                :disabled="!form['{{ $day }}'].enabled || form['{{ $day }}'].all_day || form['{{ $day }}'].by_appointment"
                                                :class="getFieldError('{{ $day }}', 'to') ? 'border-red-500 ring-2 ring-red-100' : 'border-gray-300'"
                                                class="w-full rounded-xl bg-white px-4 py-3 text-base text-gray-900 shadow-sm focus:border-pink-500 focus:ring-2 focus:ring-pink-200 disabled:bg-gray-100 disabled:text-gray-400"
                                            >
                                                <option value="">Select end time</option>
                                                @for($i = 0; $i <= 23; $i++)
                                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00">
                                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00
                                                    </option>
                                                @endfor
                                            </select>

                                            <p
                                                x-show="getFieldError('{{ $day }}', 'to')"
                                                x-text="getFieldError('{{ $day }}', 'to')"
                                                class="mt-2 text-sm text-red-600"
                                            ></p>
                                        </div>
                                    </div>

                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-3 gap-3"
                                        :class="{ 'opacity-50 pointer-events-none': !form['{{ $day }}'].enabled }"
                                    >
                                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:bg-gray-50">
                                            <input
                                                type="checkbox"
                                                x-model="form['{{ $day }}'].all_day"
                                                @change="handleAllDay('{{ $day }}')"
                                                :disabled="!form['{{ $day }}'].enabled"
                                                class="h-5 w-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                            >
                                            <span class="text-sm font-medium text-gray-700">All day</span>
                                        </label>

                                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:bg-gray-50">
                                            <input
                                                type="checkbox"
                                                x-model="form['{{ $day }}'].till_late"
                                                :disabled="!form['{{ $day }}'].enabled"
                                                class="h-5 w-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                            >
                                            <span class="text-sm font-medium text-gray-700">Till late</span>
                                        </label>

                                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:bg-gray-50">
                                            <input
                                                type="checkbox"
                                                x-model="form['{{ $day }}'].by_appointment"
                                                :disabled="!form['{{ $day }}'].enabled"
                                                class="h-5 w-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                            >
                                            <span class="text-sm font-medium text-gray-700">By appointment</span>
                                        </label>
                                    </div>

                                    <p
                                        x-show="form['{{ $day }}'].all_day"
                                        class="text-sm text-green-600"
                                    >
                                        This day is marked as available all day.
                                    </p>

                                    <p
                                        x-show="form['{{ $day }}'].by_appointment"
                                        class="text-sm text-blue-600"
                                    >
                                        This day is available by appointment only.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="inline-flex items-center justify-center rounded-xl bg-pink-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span x-show="!loading">Save availability</span>
                            <span x-show="loading">Saving...</span>
                        </button>
                    </div>
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
