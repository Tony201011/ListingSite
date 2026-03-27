<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use App\Actions\GetMyProfilePageData;
use App\Actions\GetMyProfileStepTwoData;
use App\Actions\SaveMyProfile;
use App\Http\Requests\SaveMyProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MyProfileController extends Controller
{
    public function __construct(
        private GetMyProfilePageData $getMyProfilePageData,
        private GetMyProfileStepTwoData $getMyProfileStepTwoData,
        private SaveMyProfile $saveMyProfile
    ) {
    }

    public function myProfile(): View
    {
        return view('profile.my-profile-1', $this->getMyProfilePageData->execute(Auth::user()));
    }

    public function stepTwo(): View
    {
        return view('profile.my-profile-2', $this->getMyProfileStepTwoData->execute(Auth::user()));
    }

    public function save(SaveMyProfileRequest $request): JsonResponse|RedirectResponse
    {
        $this->saveMyProfile->execute(
            Auth::user(),
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ], 200);
        }

        return redirect()
            ->route('edit-profile')
            ->with('success', 'Profile updated successfully.');
    }
}
