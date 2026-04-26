<?php

namespace App\Http\Controllers\Profile;

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
        private SaveMyProfile $saveMyProfile
    ) {}

    public function profileList(): View
    {
        $user = Auth::user();

        $this->authorize('viewAny', ProviderProfile::class);

        $profiles = $user->providerProfiles()->latest()->get();

        return view('profile.my-profiles', [
            'user' => $user,
            'profiles' => $profiles,
        ]);
    }

    public function myProfile(ProviderProfile $profile): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('viewOwned', $profile);

        return view('profile.my-profile-1', $this->getMyProfilePageData->execute($user, $profile));
    }

    public function createProfileForm(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('create', ProviderProfile::class);

        return view('profile.my-profile-2', $this->getMyProfileStepTwoData->execute($user, null));
    }

    public function storeProfile(SaveMyProfileRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', ProviderProfile::class);

        $result = $this->saveMyProfile->execute(
            Auth::user(),
            $request->validated(),
            null
        );

        if (! $result->isSuccess()) {
            abort($result->status(), $result->message() ?? 'Forbidden');
        }

        if ($request->wantsJson()) {
            return response()->json($result->toPayload(), $result->status());
        }

        $profileId = $result->data()['profile_id'] ?? null;

        if ($profileId) {
            return redirect()
                ->route('my-profile.show', ['profile' => $profileId])
                ->with('success', $result->message() ?? 'Profile created successfully.');
        }

        return redirect()
            ->route('my-profile')
            ->with('success', $result->message() ?? 'Profile created successfully.');
    }

    public function editProfile(ProviderProfile $profile): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('updateOwned', $profile);

        return view('profile.my-profile-2', $this->getMyProfileStepTwoData->execute($user, $profile));
    }

    public function save(SaveMyProfileRequest $request, ProviderProfile $profile): JsonResponse|RedirectResponse
    {
        $this->authorize('updateOwned', $profile);

        $result = $this->saveMyProfile->execute(
            Auth::user(),
            $request->validated(),
            $profile
        );

        if (! $result->isSuccess()) {
            abort($result->status(), $result->message() ?? 'Forbidden');
        }

        if ($request->wantsJson()) {
            return response()->json($result->toPayload(), $result->status());
        }

        return redirect()
            ->route('my-profile.show', ['profile' => $profile->id])
            ->with('success', $result->message() ?? 'Profile updated successfully.');
    }
}
