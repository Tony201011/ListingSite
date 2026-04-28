<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetProfileSettingPageData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileSettingController extends Controller
{
    public function __construct(
        private GetProfileSettingPageData $getProfileSettingPageData,
        private GetActiveProviderProfile $getActiveProviderProfile,
    ) {}

    public function viewProfileSetting(): View
    {
        $user = Auth::user();
        $profile = $this->getActiveProviderProfile->execute($user);

        return view(
            'profile.profile-setting',
            $this->getProfileSettingPageData->execute($user, $profile)
        );
    }
}
