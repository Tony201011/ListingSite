<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'description',
        'incall',
        'outcall',
        'extra',
        'group_id', // add this
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    public function group()
    {
        return $this->belongsTo(RateGroup::class, 'group_id');
    }
}
