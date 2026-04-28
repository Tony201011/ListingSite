<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileMessage extends Model
{
    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'message',
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
