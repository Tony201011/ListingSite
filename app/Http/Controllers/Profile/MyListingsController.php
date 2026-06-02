<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\ProviderListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyListingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::findOrFail(Auth::id());

        $listings = $user->providerListings()->latest()->get();

        return view('profile.my-listings', [
            'listings' => $listings,
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
