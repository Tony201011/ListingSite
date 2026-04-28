<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetUserAvailability;
use App\Actions\UpdateUserAvailability;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function __construct(
        private UpdateUserAvailability $updateUserAvailability,
        private GetUserAvailability $getUserAvailability,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function edit(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $saved = $this->getUserAvailability->forEdit($profile?->id ?? 0);

        return view('profile.set-your-availability', [
            'days' => $this->getUserAvailability->days(),
            'saved' => $saved,
        ]);
    }

    public function update(UpdateAvailabilityRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $this->updateUserAvailability->execute(
            $profile?->id ?? 0,
            $request->validated()['availability'] ?? []
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Availability updated successfully.',
            ]);
        }

        return redirect()
            ->route('availability.edit')
            ->with('success', 'Availability updated successfully.');
    }

    public function show(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $data = $this->getUserAvailability->forShow($profile?->id ?? 0);

        return view('profile.my-availability', $data);
    }
}
