<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ProviderProfile extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'agent_id',
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
        'account_user_referral_code',
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

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class, 'user_id', 'user_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class, 'user_id', 'user_id');
    }

    public function profileImages(): HasMany
    {
        return $this->hasMany(ProfileImage::class, 'user_id', 'user_id');
    }

    public function userVideos(): HasMany
    {
        return $this->hasMany(UserVideo::class, 'user_id', 'user_id');
    }

    public function profileMessage(): HasOne
    {
        return $this->hasOne(ProfileMessage::class, 'user_id', 'user_id');
    }

    public function searchableAs(): string
    {
        return 'provider_profiles';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(['city', 'state', 'user']);

        return [
            'id' => (string) $this->id,
            'name' => (string) ($this->name ?? ''),
            'age' => (int) ($this->age ?? 0),
            'description' => (string) ($this->description ?? ''),
            'city' => (string) ($this->city?->name ?? ''),
            'state' => (string) ($this->state?->name ?? ''),
            'suburb' => (string) ($this->user?->suburb ?? ''),
            'profile_status' => (string) ($this->profile_status ?? ''),
            'is_featured' => (bool) $this->is_featured,
            'created_at' => $this->created_at ? $this->created_at->timestamp : 0,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->profile_status === 'approved' && $this->deleted_at === null;
    }
}
