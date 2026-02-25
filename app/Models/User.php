<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PROVIDER = 'provider';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'profile_image',
        'email',
        'role',
        'is_blocked',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_blocked' => 'boolean',
            'password' => 'hashed',
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
            'provider' => $this->role === self::ROLE_PROVIDER && ! $this->is_blocked,
            default => false,
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->profile_image)) {
            return null;
        }

        return app('filesystem')->disk(config('filesystems.default', 'public'))->url($this->profile_image);
    }

    public function providerListings(): HasMany
    {
        return $this->hasMany(ProviderListing::class);
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }
}
