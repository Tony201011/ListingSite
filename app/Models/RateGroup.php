<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateGroup extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'provider_profile_id', 'name'];

    /**
     * Get the user that owns the group.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider profile this group belongs to.
     */
    public function profile()
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    /**
     * Get the rates in this group.
     */
    public function rates()
    {
        return $this->hasMany(Rate::class, 'group_id');
    }
}
