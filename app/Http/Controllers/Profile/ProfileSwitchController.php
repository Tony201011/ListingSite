<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GenerateUniqueProviderProfileSlug;
use App\Actions\GetOnlineNowState;
use App\Actions\UpdateOnlineNowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOnlineStatusRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileSwitchController extends Controller
{
    public function __construct(
        private GenerateUniqueProviderProfileSlug $generateSlug,
        private GetOnlineNowState $getOnlineNowState,
        private UpdateOnlineNowStatus $updateOnlineNowStatus,
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $profiles = $user->providerProfiles()->orderBy('id')->with([
            'primaryProfileImage',
            'photoVerification' => fn ($q) => $q->where('status', 'approved'),
        ])->get();
        $activeProfileId = session('active_provider_profile_id') ?? $profiles->first()?->id;

        $onlineStates = $profiles->mapWithKeys(function (ProviderProfile $profile): array {
            return [$profile->id => $this->getOnlineNowState->execute($profile)];
        });

        return view('profile.my-profiles', compact('profiles', 'activeProfileId', 'onlineStates'));
    }

    public function updateOnlineStatus(UpdateOnlineStatusRequest $request, ProviderProfile $profile): JsonResponse
    {
        $this->authorizeProfileOwnership($profile);

        if (
            $request->validated('status') === 'online' &&
            ! $this->isProfileReadyForOnline($profile)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your profile in Edit Profile and upload at least one photo before going online.',
            ], 403);
        }

        $result = $this->updateOnlineNowStatus->execute($profile, $request->validated('status'));

        return response()->json($result->toPayload(), $result->status());
    }

    public function selectProfile(): View|RedirectResponse
    {
        $user = Auth::user();
        $profiles = $user->providerProfiles()->orderBy('id')->with('primaryProfileImage')->get();

        // No profiles yet — send straight to dashboard to set up the first one
        if ($profiles->isEmpty()) {
            return redirect()->route('profiles.index')
                ->with('success', 'Create your first profile to get started.');
        }

        // Exactly one profile — auto-select and proceed
        if ($profiles->count() === 1) {
            session(['active_provider_profile_id' => $profiles->first()->id]);

            return redirect()->route('my-profile');
        }

        $activeProfileId = session('active_provider_profile_id') ?? $profiles->first()?->id;

        $onlineStates = $profiles->mapWithKeys(function (ProviderProfile $profile): array {
            return [$profile->id => $this->getOnlineNowState->execute($profile)];
        });

        return view('profile.select-profile', compact('profiles', 'activeProfileId', 'onlineStates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $slug = $this->generateSlug->execute($validated['name']);

        $sequence = (ProviderProfile::withTrashed()->where('slug', $slug)->max('profile_sequence') ?? 0) + 1;

        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'slug' => $slug,
            'profile_sequence' => $sequence,
            'profile_status' => 'approved',
        ]);

        session(['active_provider_profile_id' => $profile->id]);

        return redirect()->route('edit-profile')
            ->with('success', 'New profile created. Please fill in your profile details.')
            ->with('profile_form_heading', 'create');
    }

    public function switchToEdit(ProviderProfile $profile): RedirectResponse
    {
        $this->authorizeProfileOwnership($profile);

        session(['active_provider_profile_id' => $profile->id]);

        return redirect()->route('edit-profile')
            ->with('profile_form_heading', 'edit');
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

    public function destroySelected(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'profile_ids' => ['required', 'array', 'min:1'],
            'profile_ids.*' => ['integer', 'distinct'],
        ], [
            'profile_ids.required' => 'Select at least one profile to delete.',
        ]);

        $selectedIds = collect($validated['profile_ids'])
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $ownedIds = $user->providerProfiles()
            ->whereIn('id', $selectedIds)
            ->pluck('id');

        if ($ownedIds->count() !== $selectedIds->count()) {
            return back()->withErrors([
                'profile_ids' => 'You can only delete your own selected profiles.',
            ]);
        }

        $totalProfiles = $user->providerProfiles()->count();
        if ($ownedIds->count() >= $totalProfiles) {
            return back()->withErrors([
                'profile_ids' => 'You must keep at least one profile. To remove everything, delete your account.',
            ]);
        }

        $activeProfileId = (int) session('active_provider_profile_id');
        if ($activeProfileId !== 0 && $ownedIds->contains($activeProfileId)) {
            $newActive = $user->providerProfiles()
                ->whereNotIn('id', $ownedIds)
                ->orderBy('id')
                ->first();

            session(['active_provider_profile_id' => $newActive?->id]);
        }

        ProviderProfile::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $ownedIds)
            ->delete();

        $deletedCount = $ownedIds->count();

        return redirect()->route('profiles.index')
            ->with('success', $deletedCount === 1 ? 'Selected profile deleted.' : "{$deletedCount} selected profiles deleted.");
    }

    private function authorizeProfileOwnership(ProviderProfile $profile): void
    {
        if ($profile->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function isProfileReadyForOnline(ProviderProfile $profile): bool
    {
        return $this->isProfileStepOneCompleted($profile) && $this->hasProfilePhoto($profile);
    }

    private function isProfileStepOneCompleted(ProviderProfile $profile): bool
    {
        return ! empty($profile->introduction_line) &&
            ! empty($profile->profile_text) &&
            ! empty($profile->age_group_id) &&
            ! empty($profile->hair_color_id) &&
            ! empty($profile->hair_length_id) &&
            ! empty($profile->ethnicity_id) &&
            ! empty($profile->body_type_id) &&
            ! empty($profile->bust_size_id) &&
            ! empty($profile->your_length_id) &&
            ! empty($profile->availability) &&
            ! empty($profile->contact_method) &&
            ! empty($profile->phone_contact_preference) &&
            ! empty($profile->time_waster_shield) &&
            ! empty($profile->primary_identity) &&
            ! empty($profile->attributes) &&
            ! empty($profile->services_style) &&
            ! empty($profile->services_provided);
    }

    private function hasProfilePhoto(ProviderProfile $profile): bool
    {
        return $profile->profileImages()
            ->whereNull('deleted_at')
            ->exists();
    }
}
