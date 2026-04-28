<?php

namespace App\Models;

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
}
