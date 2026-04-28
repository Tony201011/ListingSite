<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetMyProfilePageData;
use App\Actions\GetMyProfileStepTwoData;
use App\Actions\SaveMyProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMyProfileRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyProfileController extends Controller
{
    public function __construct(
        private GetMyProfilePageData $getMyProfilePageData,
        private GetMyProfileStepTwoData $getMyProfileStepTwoData,
        private SaveMyProfile $saveMyProfile,
        private GetActiveProviderProfile $getActiveProviderProfile,
    ) {}

    public function myProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute($user);

        return view('profile.my-profile-1', $this->getMyProfilePageData->execute($user, $profile));
    }

    public function editProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute($user);

        return view('profile.my-profile-2', $this->getMyProfileStepTwoData->execute($user, $profile));
    }

    public function save(SaveMyProfileRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $user = Auth::user();

        $activeProfile = $this->getActiveProviderProfile->execute($user);

        $result = $this->saveMyProfile->execute(
            $user,
            $request->validated(),
            $activeProfile
        );

        if (! $result->isSuccess()) {
            abort($result->status(), $result->message() ?? 'Forbidden');
        }

        if (isset($result->data()['profile_id'])) {
            session(['active_provider_profile_id' => $result->data()['profile_id']]);
        }

        if ($request->wantsJson()) {
            return response()->json($result->toPayload(), $result->status());
        }

        return redirect()
            ->route('edit-profile')
            ->with('success', $result->message() ?? 'Profile updated successfully.');
    }
}
