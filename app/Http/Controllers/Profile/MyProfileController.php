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

    public function myProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            return redirect('/admin');
        }

        if ($user && $user->role === \App\Models\User::ROLE_AGENT) {
            return redirect('/agent');
        }

        $this->authorize('view', ProviderProfile::class);

        return view('profile.my-profile-1', $this->getMyProfilePageData->execute($user));
    }

    public function editProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->role === \App\Models\User::ROLE_ADMIN) {
            return redirect('/admin');
        }

        if ($user && $user->role === \App\Models\User::ROLE_AGENT) {
            return redirect('/agent');
        }

        $this->authorize('view', ProviderProfile::class);

        return view('profile.my-profile-2', $this->getMyProfileStepTwoData->execute($user));
    }

    public function save(SaveMyProfileRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $result = $this->saveMyProfile->execute(
            Auth::user(),
            $request->validated()
        );

        if (! $result->isSuccess()) {
            abort($result->status(), $result->message() ?? 'Forbidden');
        }

        if ($request->wantsJson()) {
            return response()->json($result->toPayload(), $result->status());
        }

        return redirect()
            ->route('edit-profile')
            ->with('success', $result->message() ?? 'Profile updated successfully.');
    }
}
