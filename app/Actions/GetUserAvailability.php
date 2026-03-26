<?php

namespace App\Actions;

use App\Models\Availability;
use Illuminate\Database\Eloquent\Collection;
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

    public function forEdit(int $userId): SupportCollection
    {
        return Availability::where('user_id', $userId)
            ->get()
            ->keyBy('day');
    }

    public function forShow(int $userId): array
    {
        $availabilities = Availability::where('user_id', $userId)
            ->orderByRaw("
                FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
            ")
            ->get();

        $availabilityCount = Availability::where('user_id', $userId)->count();

        return [
            'availabilities' => $availabilities,
            'availabilityCount' => $availabilityCount,
        ];
    }
}
