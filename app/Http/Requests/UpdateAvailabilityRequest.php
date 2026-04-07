<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateAvailabilityRequest extends FormRequest
{
    protected array $days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'availability' => ['nullable', 'array'],
            'availability.*' => ['nullable', 'array'],
            'availability.*.enabled' => ['nullable'],
            'availability.*.from' => ['nullable', 'date_format:H:i'],
            'availability.*.to' => ['nullable', 'date_format:H:i'],
            'availability.*.till_late' => ['nullable'],
            'availability.*.all_day' => ['nullable'],
            'availability.*.by_appointment' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'availability.array' => 'Availability data must be an array.',
            'availability.*.from.date_format' => 'The from time must be in HH:MM format.',
            'availability.*.to.date_format' => 'The to time must be in HH:MM format.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $availability = $this->input('availability', []);

            foreach ($availability as $day => $dayData) {
                if (! in_array($day, $this->days, true)) {
                    $validator->errors()->add("availability.$day", 'Invalid day provided.');

                    continue;
                }

                $enabled = ! empty($dayData['enabled']);
                $allDay = ! empty($dayData['all_day']);
                $tillLate = ! empty($dayData['till_late']);
                $byAppointment = ! empty($dayData['by_appointment']);
                $from = $dayData['from'] ?? null;
                $to = $dayData['to'] ?? null;

                if (! $enabled) {
                    continue;
                }

                if ($allDay || $byAppointment || $tillLate) {
                    continue;
                }

                if ((filled($from) && ! filled($to)) || (! filled($from) && filled($to))) {
                    $validator->errors()->add(
                        "availability.$day.from",
                        "Both from and to times are required for {$day} unless all day is selected."
                    );
                }

                if (filled($from) && filled($to) && $from >= $to) {
                    $validator->errors()->add(
                        "availability.$day.to",
                        "The to time must be later than the from time for {$day}."
                    );
                }
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->ajax() || $this->wantsJson()) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
