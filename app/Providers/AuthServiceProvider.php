<?php

namespace App\Providers;

use App\Models\PhotoVerification;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\ShortUrl;
use App\Models\Tour;
use App\Models\User;
use App\Models\UserVideo;
use App\Policies\PhotoVerificationPolicy;
use App\Policies\ProfileImagePolicy;
use App\Policies\ProviderProfilePolicy;
use App\Policies\RateGroupPolicy;
use App\Policies\RatePolicy;
use App\Policies\ShortUrlPolicy;
use App\Policies\TourPolicy;
use App\Policies\UserVideoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Rate::class => RatePolicy::class,
        RateGroup::class => RateGroupPolicy::class,
        ProfileImage::class => ProfileImagePolicy::class,
        UserVideo::class => UserVideoPolicy::class,
        ProviderProfile::class => ProviderProfilePolicy::class,
        Tour::class => TourPolicy::class,
        PhotoVerification::class => PhotoVerificationPolicy::class,
        ShortUrl::class => ShortUrlPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Reviewer accounts are read-only. Deny all state-mutating Gate abilities so that
        // Filament's built-in canCreate / canEdit / canDelete methods (which go through the
        // Gate) all return false without touching individual resource files.
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->isReviewer() && in_array($ability, [
                'create',
                'update',
                'delete',
                'forceDelete',
                'restore',
                'replicate',
            ], true)) {
                return false;
            }

            return null; // Fall through to normal gate/policy checks
        });
    }
}
