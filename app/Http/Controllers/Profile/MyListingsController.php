<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Actions\GetActiveProviderProfile;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MyListingsController extends Controller
{
    public function __construct(private GetActiveProviderProfile $getActiveProviderProfile) {}

    public function index(Request $request): View
    {
        $user = User::findOrFail(Auth::id());
        $activeProfile = $this->getActiveProviderProfile->execute($user);
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'oldest');

        $baseQuery = ProviderListing::where('user_id', $user->id);

        if ($activeProfile && Schema::hasColumn('provider_listings', 'provider_profile_id')) {
            $baseQuery->where('provider_profile_id', $activeProfile->id);
        }

        $statusCounts = [
            'all' => (clone $baseQuery)->count(),
            'online' => (clone $baseQuery)->where('is_live', true)->where('is_active', true)->count(),
            'offline' => (clone $baseQuery)->where(fn ($q) => $q->where('is_live', false)->orWhere('is_active', false))->count(),
        ];

        $listings = (clone $baseQuery)
            ->when($status === 'online', fn ($query) => $query->where('is_live', true)->where('is_active', true))
            ->when($status === 'offline', fn ($query) => $query->where(fn ($q) => $q->where('is_live', false)->orWhere('is_active', false)))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('website_type', 'like', "%{$search}%")
                        ->orWhereHas('providerProfile', function ($profileQuery) use ($search) {
                            $profileQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('suburb', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%")
                                ->orWhere('introduction_line', 'like', "%{$search}%")
                                ->orWhere('profile_text', 'like', "%{$search}%");
                        });
                });
            })
            ->when($sort === 'newest', fn ($query) => $query->orderByDesc('created_at'), fn ($query) => $query->orderBy('created_at'))
            ->with('providerProfile')
            ->get();

        // Also provide provider profiles so the UI can fall back to showing
        // profiles (the select-profile view) when no provider_listings exist.
        $profiles = $user->providerProfiles()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('suburb', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('introduction_line', 'like', "%{$search}%")
                        ->orWhere('profile_text', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->with('primaryProfileImage')
            ->get();

        return view('profile.my-listings', [
            'listings' => $listings,
            'profiles' => $profiles,
            'activeProfileId' => $activeProfile?->id ?? null,
            'status' => $status,
            'statusCounts' => $statusCounts,
            'searchQuery' => $search,
            'sort' => $sort,
        ]);
    }

    public function show(ProviderListing $listing): View
    {
        $this->authorizeOwnership($listing);

        return view('profile.my-listings-show', [
            'listing' => $listing->loadMissing('providerProfile.state', 'providerProfile.city', 'providerProfile.primaryProfileImage'),
        ]);
    }

    public function showProfile(ProviderProfile $profile): View
    {
        $this->authorizeProfileOwnership($profile);

        return view('profile.my-listings-profile-show', [
            'profile' => $profile->loadMissing('state', 'city', 'primaryProfileImage'),
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

    private function authorizeProfileOwnership(ProviderProfile $profile): void
    {
        if ($profile->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
