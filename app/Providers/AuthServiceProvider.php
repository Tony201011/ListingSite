<?php
namespace App\Providers;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\ProfileImage;
use App\Models\UserVideo;
use App\Models\ProviderProfile;
use App\Models\Tour;
use App\Policies\ProviderProfilePolicy;
use App\Policies\TourPolicy;
use App\Policies\RateGroupPolicy;
use App\Policies\ProfileImagePolicy;
use App\Policies\RatePolicy;
use App\Policies\UserVideoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Rate::class => RatePolicy::class,
        RateGroup::class => RateGroupPolicy::class,
        ProfileImage::class => ProfileImagePolicy::class,
        UserVideo::class => UserVideoPolicy::class,
        ProviderProfile::class => ProviderProfilePolicy::class,
        Tour::class => TourPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
