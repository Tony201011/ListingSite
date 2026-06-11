<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class ProviderProfile extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    /**
     * Cache of public profile counts grouped by slug and location key.
     *
     * @var array<string, array<string, int>>
     */
    private static array $publicSlugLocationCounts = [];

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'profile_sequence',
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
        'suburb',
        'latitude',
        'longitude',
        'phone',
        'whatsapp',
        'account_user_referral_code',
        'is_verified',
        'is_featured',
        'featured_expires_at',
        'free_listing_expires_at',
        'home_featured_expires_at',
        'local_banner_expires_at',
        'home_banner_expires_at',
        'membership_id',
        'credits',
        'profile_status',
        'expires_at',
        'hold_reason',
        'anonymized_at',
        'is_blocked',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'country_id' => 'integer',
            'state_id' => 'integer',
            'city_id' => 'integer',
            'profile_sequence' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'featured_expires_at' => 'datetime',
            'free_listing_expires_at' => 'datetime',
            'home_featured_expires_at' => 'datetime',
            'local_banner_expires_at' => 'datetime',
            'home_banner_expires_at' => 'datetime',
            'is_blocked' => 'boolean',
            'membership_id' => 'integer',
            'credits' => 'integer',
            'expires_at' => 'datetime',
            'anonymized_at' => 'datetime',
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
        return $this->hasMany(Rate::class, 'provider_profile_id');
    }

    public function rateGroups(): HasMany
    {
        return $this->hasMany(RateGroup::class, 'provider_profile_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class, 'provider_profile_id')
            ->orderedByWeekday();
    }

    public function profileImages(): HasMany
    {
        return $this->hasMany(ProfileImage::class, 'provider_profile_id');
    }

    public function primaryProfileImage(): HasOne
    {
        return $this->hasOne(ProfileImage::class, 'provider_profile_id')->where('is_primary', true);
    }

    public function userVideos(): HasMany
    {
        return $this->hasMany(UserVideo::class, 'provider_profile_id');
    }

    public function profileMessage(): HasOne
    {
        return $this->hasOne(ProfileMessage::class, 'provider_profile_id');
    }

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class, 'provider_profile_id');
    }

    public function photoVerification(): HasMany
    {
        return $this->hasMany(PhotoVerification::class, 'provider_profile_id');
    }

    public function onlineUser(): HasOne
    {
        return $this->hasOne(OnlineUser::class, 'provider_profile_id');
    }

    public function onlineUsers(): HasMany
    {
        return $this->hasMany(OnlineUser::class, 'provider_profile_id');
    }

    public function providerOnlineLogs(): HasMany
    {
        return $this->hasMany(ProviderOnlineLog::class, 'provider_profile_id');
    }

    public function availableNow(): HasOne
    {
        return $this->hasOne(AvailableNow::class, 'provider_profile_id');
    }

    public function hideShowProfile(): HasOne
    {
        return $this->hasOne(HideShowProfile::class, 'provider_profile_id');
    }

    public function setAndForget(): HasOne
    {
        return $this->hasOne(SetAndForget::class, 'provider_profile_id');
    }

    public function shortUrl(): HasOne
    {
        return $this->hasOne(ShortUrl::class, 'provider_profile_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'provider_profile_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(UserReport::class);
    }

    public function scopeWhereCurrentlyOnline(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            // Profile-linked online row (standard mechanism)
            $q->whereHas(
                'onlineUsers',
                fn (Builder $onlineQuery): Builder => $onlineQuery
                    ->whereNotNull('provider_profile_id')
                    ->where('status', 'online')
            )
            // OR: no profile-linked row at all AND user has a legacy online row
                ->orWhere(function (Builder $q): void {
                    $q->whereDoesntHave(
                        'onlineUsers',
                        fn (Builder $onlineQuery): Builder => $onlineQuery->whereNotNull('provider_profile_id')
                    )->whereHas(
                        'user',
                        fn (Builder $userQuery): Builder => $userQuery->whereHas(
                            'legacyOnlineUsers',
                            fn (Builder $onlineQuery): Builder => $onlineQuery->where('status', 'online')
                        )
                    );
                });
        });
    }

    public function scopeWhereCurrentlyOffline(Builder $query): Builder
    {
        // Offline = no profile-linked online row AND NOT (no profile-linked row + legacy online row)
        return $query->whereDoesntHave(
            'onlineUsers',
            fn (Builder $onlineQuery): Builder => $onlineQuery
                ->whereNotNull('provider_profile_id')
                ->where('status', 'online')
        )->where(function (Builder $q): void {
            // Either has a profile-linked row (but it's not online) …
            $q->whereHas(
                'onlineUsers',
                fn (Builder $onlineQuery): Builder => $onlineQuery->whereNotNull('provider_profile_id')
            )
            // … or the user has no legacy online row
                ->orWhereDoesntHave(
                    'user',
                    fn (Builder $userQuery): Builder => $userQuery->whereHas(
                        'legacyOnlineUsers',
                        fn (Builder $onlineQuery): Builder => $onlineQuery->where('status', 'online')
                    )
                );
        });
    }

    public function scopeWhereCurrentlyAvailableNow(Builder $query): Builder
    {
        return $query->whereHas(
            'availableNow',
            fn (Builder $availableQuery): Builder => $availableQuery
                ->where('status', 'online')
                ->whereNotNull('available_expires_at')
                ->where('available_expires_at', '>', now())
        );
    }

    public function scopeWhereCurrentlyUnavailableNow(Builder $query): Builder
    {
        return $query->whereDoesntHave(
            'availableNow',
            fn (Builder $availableQuery): Builder => $availableQuery
                ->where('status', 'online')
                ->whereNotNull('available_expires_at')
                ->where('available_expires_at', '>', now())
        );
    }

    public function isCurrentlyOnline(): bool
    {
        // Check profile-linked row first
        if ($this->relationLoaded('onlineUsers')) {
            if ($this->onlineUsers->contains(fn (OnlineUser $onlineUser): bool => $onlineUser->isCurrentlyOnline())) {
                return true;
            }
            // Has a profile-linked row (not online) — don't fall through to legacy
            if ($this->onlineUsers->isNotEmpty()) {
                return false;
            }
        } else {
            if ($this->onlineUsers()->whereNotNull('provider_profile_id')->where('status', 'online')->exists()) {
                return true;
            }
            // Has any profile-linked row — don't fall through to legacy
            if ($this->onlineUsers()->whereNotNull('provider_profile_id')->exists()) {
                return false;
            }
        }

        // No profile-linked row at all — check legacy user-level online row
        return $this->user()->whereHas(
            'legacyOnlineUsers',
            fn (Builder $q): Builder => $q->where('status', 'online')
        )->exists();
    }

    public function isCurrentlyAvailableNow(): bool
    {
        if ($this->relationLoaded('availableNow')) {
            return $this->availableNow?->isCurrentlyAvailable() ?? false;
        }

        return $this->availableNow()
            ->where('status', 'online')
            ->whereNotNull('available_expires_at')
            ->where('available_expires_at', '>', now())
            ->exists();
    }

    // -----------------------------------------------------------------------
    // Escort URL helpers
    // -----------------------------------------------------------------------

    /**
     * Return the lowercase, 2-3 character state abbreviation for use in
     * the escort URL (e.g. "vic", "nsw", "qld"). Falls back to parsing the
     * state from suburb text (e.g. "Melbourne, VIC 3000"), then to "au".
     */
    public function getStateSlug(): string
    {
        static $map = [
            'australian capital territory' => 'act',
            'act' => 'act',
            'new south wales' => 'nsw',
            'nsw' => 'nsw',
            'victoria' => 'vic',
            'vic' => 'vic',
            'queensland' => 'qld',
            'qld' => 'qld',
            'western australia' => 'wa',
            'wa' => 'wa',
            'south australia' => 'sa',
            'sa' => 'sa',
            'tasmania' => 'tas',
            'tas' => 'tas',
            'northern territory' => 'nt',
            'nt' => 'nt',
        ];

        $stateName = trim((string) ($this->state?->name ?? ''));

        if ($stateName === '') {
            $suburb = trim((string) ($this->suburb ?? ''));

            if (str_contains($suburb, ',')) {
                $parts = array_values(array_filter(array_map('trim', explode(',', $suburb))));
                $locationTail = strtolower((string) end($parts));

                if ($locationTail !== '') {
                    if (preg_match('/\b(act|nsw|vic|qld|wa|sa|tas|nt)\b/i', $locationTail, $matches) === 1) {
                        return strtolower($matches[1]);
                    }

                    $stateName = trim((string) preg_replace('/\s+\d{4}\b/', '', (string) end($parts)));
                }
            }
        }

        $normalizedStateName = strtolower(trim(preg_replace('/\s+/', ' ', $stateName) ?? $stateName));

        return $map[$normalizedStateName] ?? (Str::slug($stateName) ?: 'au');
    }

    /**
     * Return the slugified suburb/city name for use in the escort URL
     * (e.g. "melbourne", "sydney").  Falls back to "australia" when neither
     * a city record nor a suburb string is available.
     */
    public function getSuburbSlug(): string
    {
        $cityName = $this->city?->name ?? '';

        if ($cityName === '') {
            $suburb = $this->suburb ?? '';
            $cityName = str_contains($suburb, ',')
                ? trim(explode(',', $suburb, 2)[0])
                : $suburb;
        }

        return Str::slug($cityName) ?: 'australia';
    }

    /**
     * Return the zero-padded 3-digit sequence string used in the URL.
     * The URL segment is (profile_sequence - 1), so the second profile
     * (profile_sequence = 2) maps to "001", the third to "002", etc.
     * The primary profile (profile_sequence = 1) always uses the clean URL
     * and never calls this method.
     */
    public function getSequenceFormatted(): string
    {
        return str_pad((string) max(($this->profile_sequence ?? 1) - 1, 0), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Determine whether this profile needs the sequence segment to keep the URL
     * unique for the current slug + location.
     *
     * The primary profile (profile_sequence = 1) always uses the clean URL,
     * regardless of how many profiles share the same slug.
     */
    public function shouldIncludeSequenceInUrl(): bool
    {
        // Primary profile never needs a sequence segment in the URL
        if (($this->profile_sequence ?? 1) <= 1) {
            return false;
        }

        $slug = trim((string) $this->slug);

        if ($slug === '') {
            return true;
        }

        $this->loadMissing(['state', 'city']);

        $locationKey = $this->getStateSlug().'|'.$this->getSuburbSlug();

        if (! array_key_exists($slug, self::$publicSlugLocationCounts)) {
            $profiles = self::query()
                ->where('slug', $slug)
                ->where('profile_status', 'approved')
                ->where('is_blocked', false)
                ->whereHas('user')
                ->whereDoesntHave('hideShowProfile', fn ($query) => $query->where('status', 'hide'))
                ->with(['state', 'city'])
                ->get(['id', 'slug', 'state_id', 'city_id', 'suburb']);

            $counts = [];

            foreach ($profiles as $profile) {
                $key = $profile->getStateSlug().'|'.$profile->getSuburbSlug();
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }

            self::$publicSlugLocationCounts[$slug] = $counts;
        }

        $counts = self::$publicSlugLocationCounts[$slug];
        $matchedLocationCount = $counts[$locationKey] ?? 0;

        if ($matchedLocationCount > 0) {
            return $matchedLocationCount > 1;
        }

        return array_sum($counts) > 1;
    }

    /**
     * Return the full SEO-friendly escort profile URL:
     *   /escorts/{state}/{suburb}/{slug}
     * or /escorts/{state}/{suburb}/{slug}/{sequence_id} when needed for uniqueness.
     *
     * Relations `state` and `city` are lazy-loaded if not already loaded.
     */
    public function getEscortUrl(): string
    {
        $this->loadMissing(['state', 'city']);

        $routeParams = [
            'state' => $this->getStateSlug(),
            'suburb' => $this->getSuburbSlug(),
            'slug' => $this->slug,
        ];

        if ($this->shouldIncludeSequenceInUrl()) {
            $routeParams['sequence_id'] = $this->getSequenceFormatted();

            return route('profile.show', $routeParams);
        }

        return route('profile.show.no-sequence', $routeParams);
    }

    // -----------------------------------------------------------------------

    public function searchableAs(): string
    {
        return 'provider_profiles';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(['city', 'state']);

        return [
            'id' => (string) $this->id,
            'name' => (string) ($this->name ?? ''),
            'age' => (int) ($this->age ?? 0),
            'description' => (string) ($this->description ?? ''),
            'city' => (string) ($this->city?->name ?? ''),
            'state' => (string) ($this->state?->name ?? ''),
            'suburb' => (string) ($this->suburb ?? ''),
            'profile_status' => (string) ($this->profile_status ?? ''),
            'is_featured' => (bool) $this->is_featured,
            'created_at' => $this->created_at ? $this->created_at->timestamp : 0,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->profile_status === 'approved' && $this->deleted_at === null && ! $this->is_blocked;
    }
}
