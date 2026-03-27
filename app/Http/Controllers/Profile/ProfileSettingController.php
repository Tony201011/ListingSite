<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

use App\Actions\GetProfileSettingPageData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileSettingController extends Controller
{
    public function __construct(
        private GetProfileSettingPageData $getProfileSettingPageData
    ) {
    }

    public function viewProfileSetting(): View
    {
        return view(
            'profile.profile-setting',
            $this->getProfileSettingPageData->execute(auth::user())
        );
    }
}
