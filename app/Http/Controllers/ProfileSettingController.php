<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileSettingController extends Controller
{
    //
            public function viewProfileSetting(Request $request)
        {
            $user = Auth::user()?->load('providerProfile');
            $profile = $user?->providerProfile;

            $ids = array_filter([
                $profile?->age_group_id,
                $profile?->hair_color_id,
                $profile?->hair_length_id,
                $profile?->ethnicity_id,
                $profile?->body_type_id,
                $profile?->bust_size_id,
                $profile?->your_length_id,
            ]);

            $categories = Category::whereIn('id', $ids)->pluck('name', 'id');

            $userInfo = [
                'user' => $user,
                'provider_profile' => $profile,
                'age_group_name' => $categories[$profile?->age_group_id] ?? null,
                'hair_color_name' => $categories[$profile?->hair_color_id] ?? null,
                'hair_length_name' => $categories[$profile?->hair_length_id] ?? null,
                'ethnicity_name' => $categories[$profile?->ethnicity_id] ?? null,
                'body_type_name' => $categories[$profile?->body_type_id] ?? null,
                'bust_size_name' => $categories[$profile?->bust_size_id] ?? null,
                'your_length_name' => $categories[$profile?->your_length_id] ?? null,
            ];

            $profileImage = $user?->profileImages()->whereNull('deleted_at')->get()->toArray() ?? [];
            $videos = UserVideo::where('user_id', Auth::id())->latest()->get()->toArray();

            $photoVerification = $user?->photoVerification()
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->count() > 1;

           // dd($userInfo);

            return view('view-profile-setting', compact('profileImage', 'videos', 'photoVerification', 'userInfo'));
        }
}
