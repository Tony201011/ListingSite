<?php

namespace App\Http\Controllers;
use App\Models\SiteSetting;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UrlController extends Controller
{
    public function shortUrl(Request $request)
    {
        $user = Auth::user();

        $siteSetting = SiteSetting::query()->latest('updated_at')->first();

      //  dd($siteSetting);
        $siteSetting = $siteSetting?->short_url ?? false;

        if (!$user) {
            return redirect('/signin');
        }

        // Check if user already has a short URL
        $shortUrlRecord = ShortUrl::where('user_id', $user->id)->first();

        if (!$shortUrlRecord) {
            // Generate a unique slug: md5 of name + id (or any unique combination)
            $slug = md5($user->name . $user->id); // e.g., 5d41402abc4b2a76b9719d911017c592
            // Optionally truncate or make it more readable, but md5 is fine for uniqueness

            // Ensure uniqueness (though md5 collision is extremely unlikely)
            while (ShortUrl::where('short_url', $slug)->exists()) {
                // If by some miracle collision, append a random number
                $slug = md5($user->name . $user->id . rand(1, 9999));
            }

            $shortUrlRecord = ShortUrl::create([
                'user_id' => $user->id,
                'short_url' => $slug,
            ]);
        }

        $slug = $shortUrlRecord->short_url;

        return view('short-url', compact('slug', 'siteSetting'));
    }

    public function updateShortUrl(Request $request)
    {

        $user = Auth::user();

        $user_id = $user->id;


        $request->validate([
            'slug' => 'required|alpha_dash|unique:short_urls,short_url,' . $user_id . ',user_id',
            // alpha_dash allows letters, numbers, dashes, underscores – adjust as needed
        ]);



        ShortUrl::updateOrCreate(
            ['user_id' => $user->id],
            ['short_url' => $request->slug]
        );

        return response()->json([
            'success' => true,
            'message' => 'Short URL updated successfully.',
            'slug' => $request->slug,
        ]);
    }
}
