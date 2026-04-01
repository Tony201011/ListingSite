<?php

namespace App\Models;

use App\Notifications\BrandedResetPasswordNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_AGENT = 'agent';

    public const ROLE_PROVIDER = 'provider';

    protected $fillable = [
        'name',
        'profile_image',
        'email',
        'role',
        'is_blocked',
        'mobile',
        'suburb',
        'referral_code',
        'mobile_verified',
        'password',
        'account_status',
        'hold_reason',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_blocked' => 'boolean',
            'mobile_verified' => 'boolean',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
            'scheduled_purge_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            $user->role ??= self::ROLE_PROVIDER;
            $user->is_blocked ??= false;
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->role === self::ROLE_ADMIN,
            'agent' => $this->role === self::ROLE_AGENT && ! $this->is_blocked,
            default => false,
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->profile_image)) {
            return null;
        }

        return app('filesystem')->disk(config('media.avatar_disk', 'public'))->url($this->profile_image);
    }

    public function providerListings(): HasMany
    {
        return $this->hasMany(ProviderListing::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }

    public function managedProfiles(): HasMany
    {
        return $this->hasMany(ProviderProfile::class, 'agent_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new BrandedResetPasswordNotification($token));
    }

    public function rateGroups(): HasMany
    {
        return $this->hasMany(RateGroup::class);
    }

    public function profileMessage(): HasOne
    {
        return $this->hasOne(ProfileMessage::class);
    }

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(\App\Models\Availability::class);
    }

    public function profileImages(): HasMany
    {
        return $this->hasMany(ProfileImage::class);
    }

    public function photoVerification(): HasMany
    {
        return $this->hasMany(PhotoVerification::class);
    }

    public function primaryProfileImage(): HasOne
    {
        return $this->hasOne(ProfileImage::class)->where('is_primary', true);
    }
}
