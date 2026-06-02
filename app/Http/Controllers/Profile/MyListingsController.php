<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Actions\GetActiveProviderProfile;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyListingsController extends Controller
{
    public function __construct(private GetActiveProviderProfile $getActiveProviderProfile) {}

    public function index(Request $request): View
    {
        $user = User::findOrFail(Auth::id());

        $listings = $user->providerListings()->latest()->get();

        // Also provide provider profiles so the UI can fall back to showing
        // profiles (the select-profile view) when no provider_listings exist.
        $profiles = $user->providerProfiles()->orderBy('id')->with('primaryProfileImage')->get();
        $activeProfile = $this->getActiveProviderProfile->execute($user);

        return view('profile.my-listings', [
            'listings' => $listings,
            'profiles' => $profiles,
            'activeProfileId' => $activeProfile?->id ?? null,
        ]);
    }

    public function show(ProviderListing $listing): View
    {
        $this->authorizeOwnership($listing);

        return view('profile.my-listings-show', [
            'listing' => $listing,
        ]);
    }

    public function feature(Request $request, ProviderListing $listing)
    {
        $this->authorizeOwnership($listing);

        $validated = $request->validate([
            'feature' => ['required', 'string', 'in:top,premium'],
        ]);

        if ($validated['feature'] === 'top') {
            $listing->is_live = true;
            $listing->save();

            return redirect()->back()->with('success', 'Listing has been marked Online.');
        }

        $listing->is_vip = true;
        $listing->save();

        return redirect()->back()->with('success', 'Listing has been upgraded to Premium.');
    }

    private function authorizeOwnership(ProviderListing $listing): void
    {
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
