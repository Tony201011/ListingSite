<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GenerateUniqueProviderProfileSlug;
use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileSwitchController extends Controller
{
    public function __construct(
        private GenerateUniqueProviderProfileSlug $generateSlug,
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $profiles = $user->providerProfiles()->orderBy('id')->get();
        $activeProfileId = session('active_provider_profile_id') ?? $profiles->first()?->id;

        return view('profile.my-profiles', compact('profiles', 'activeProfileId'));
    }

    public function store(): RedirectResponse
    {
        $user = Auth::user();

        $slug = $this->generateSlug->execute($user->name);

        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => $slug,
        ]);

        session(['active_provider_profile_id' => $profile->id]);

        return redirect()->route('edit-profile')
            ->with('success', 'New profile created. Please fill in your profile details.');
    }

    public function switchTo(ProviderProfile $profile): RedirectResponse
    {
        $this->authorizeProfileOwnership($profile);

        session(['active_provider_profile_id' => $profile->id]);

        return redirect()->route('my-profile')
            ->with('success', 'Switched to profile: '.$profile->name);
    }

    public function destroy(ProviderProfile $profile): RedirectResponse
    {
        $this->authorizeProfileOwnership($profile);

        if (Auth::user()->providerProfiles()->count() <= 1) {
            return back()->with('error', 'You cannot delete your only profile.');
        }

        if ((int) session('active_provider_profile_id') === $profile->id) {
            $newActive = Auth::user()->providerProfiles()
                ->where('id', '!=', $profile->id)
                ->orderBy('id')
                ->first();
            session(['active_provider_profile_id' => $newActive?->id]);
        }

        $profile->delete();

        return redirect()->route('profiles.index')
            ->with('success', 'Profile deleted.');
    }

    private function authorizeProfileOwnership(ProviderProfile $profile): void
    {
        if ($profile->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
