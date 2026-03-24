<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProviderProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'age',
        'description',
        'introduction_line',
        'profile_text',
        'primary_identity',
        'attributes',
        'services_style',
        'services_provided',
        'age_group_id',
        'hair_color_id',
        'hair_length_id',
        'ethnicity_id',
        'body_type_id',
        'bust_size_id',
        'your_length_id',
        'availability',
        'contact_method',
        'phone_contact_preference',
        'time_waster_shield',
        'twitter_handle',
        'website',
        'onlyfans_username',
        'country_id',
        'state_id',
        'city_id',
        'latitude',
        'longitude',
        'phone',
        'whatsapp',
        'is_verified',
        'is_featured',
        'membership_id',
        'profile_status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'country_id' => 'integer',
            'state_id' => 'integer',
            'city_id' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'membership_id' => 'integer',
            'expires_at' => 'datetime',
            'primary_identity' => 'array',
            'attributes' => 'array',
            'services_style' => 'array',
            'services_provided' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
