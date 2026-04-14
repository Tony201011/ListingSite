<?php

namespace App\Actions;

use App\Models\SetAndForget;
use App\Models\User;

class GetSetAndForgetState
{
    public function execute(?User $user): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $defaults = [
            'online_now_enabled' => false,
            'online_now_days' => [],
            'online_now_time' => '',
            'available_now_enabled' => false,
            'available_now_days' => [],
            'available_now_time' => '',
        ];

        if (! $user) {
            return array_merge($defaults, compact('days'));
        }

        $record = SetAndForget::firstWhere('user_id', $user->id);

        return [
            'days' => $days,
            'online_now_enabled' => (bool) ($record?->online_now_enabled ?? false),
            'online_now_days' => $record?->online_now_days ?? [],
            'online_now_time' => $record?->online_now_time ?? '',
            'available_now_enabled' => (bool) ($record?->available_now_enabled ?? false),
            'available_now_days' => $record?->available_now_days ?? [],
            'available_now_time' => $record?->available_now_time ?? '',
        ];
    }
}
