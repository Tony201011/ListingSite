<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetAndForget extends Model
{
    protected $table = 'set_and_forgets';

    protected $fillable = [
        'user_id',
        'online_now_enabled',
        'online_now_days',
        'online_now_time',
        'available_now_enabled',
        'available_now_days',
        'available_now_time',
    ];

    protected $casts = [
        'online_now_enabled' => 'boolean',
        'online_now_days' => 'array',
        'available_now_enabled' => 'boolean',
        'available_now_days' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shouldTriggerOnlineNow(): bool
    {
        if (! $this->online_now_enabled) {
            return false;
        }

        return $this->matchesSchedule($this->online_now_days, $this->online_now_time);
    }

    public function shouldTriggerAvailableNow(): bool
    {
        if (! $this->available_now_enabled) {
            return false;
        }

        return $this->matchesSchedule($this->available_now_days, $this->available_now_time);
    }

    private function matchesSchedule(?array $days, ?string $time): bool
    {
        if (empty($days) || blank($time)) {
            return false;
        }

        $currentDay = now()->format('l');
        $currentTime = now()->format('H:i');

        return in_array($currentDay, $days, true) && $currentTime === $time;
    }
}
