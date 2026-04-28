<?php

namespace App\Actions;

use App\Models\Availability;
use Illuminate\Support\Collection as SupportCollection;

class GetUserAvailability
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

    public function days(): array
    {
        return $this->days;
    }

    public function forEdit(int $profileId): SupportCollection
    {
        return Availability::where('provider_profile_id', $profileId)
            ->get()
            ->keyBy('day');
    }

    public function forShow(int $profileId): array
    {
        $availabilities = Availability::where('provider_profile_id', $profileId)
            ->orderByRaw("
                FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
            ")
            ->get();

        $availabilityCount = Availability::where('provider_profile_id', $profileId)->count();

        return [
            'availabilities' => $availabilities,
            'availabilityCount' => $availabilityCount,
        ];
    }
}
