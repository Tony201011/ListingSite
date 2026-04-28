<?php

namespace App\Actions;

use App\Models\Availability;
use Illuminate\Support\Facades\DB;

class UpdateUserAvailability
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

    public function execute(int $profileId, array $availabilityData): void
    {
        DB::transaction(function () use ($profileId, $availabilityData) {
            $normalizedData = [];
            foreach ($availabilityData as $key => $value) {
                $normalizedData[ucfirst(strtolower($key))] = $value;
            }

            foreach ($this->days as $day) {
                $dayData = $normalizedData[$day] ?? [];

                $payload = $this->buildPayload($dayData);

                Availability::updateOrCreate(
                    [
                        'provider_profile_id' => $profileId,
                        'day' => $day,
                    ],
                    $payload
                );
            }
        });
    }

    protected function buildPayload(array $dayData): array
    {
        $enabled = ! empty($dayData['enabled']);
        $allDay = ! empty($dayData['all_day']);
        $tillLate = ! empty($dayData['till_late']);
        $byAppointment = ! empty($dayData['by_appointment']);

        $fromTime = $dayData['from'] ?? null;
        $toTime = $dayData['to'] ?? null;

        if (! $enabled) {
            $fromTime = null;
            $toTime = null;
            $allDay = false;
            $tillLate = false;
            $byAppointment = false;
        }

        if ($allDay) {
            $fromTime = null;
            $toTime = null;
        }

        if ($tillLate) {
            $toTime = null;
        }

        return [
            'enabled' => $enabled,
            'from_time' => $fromTime,
            'to_time' => $toTime,
            'till_late' => $tillLate,
            'all_day' => $allDay,
            'by_appointment' => $byAppointment,
        ];
    }
}
