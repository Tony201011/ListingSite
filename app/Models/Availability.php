<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'day',
        'enabled',
        'from_time',
        'to_time',
        'till_late',
        'all_day',
        'by_appointment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    public function scopeOrderedByWeekday(Builder $query): Builder
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $caseSql = collect($days)
            ->values()
            ->map(fn (string $day, int $index): string => 'WHEN ? THEN '.($index + 1))
            ->implode(' ');

        return $query->orderByRaw("CASE day {$caseSql} ELSE 8 END", $days);
    }
}
