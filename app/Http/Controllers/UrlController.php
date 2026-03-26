<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShortUrlRequest;
use App\Models\ShortUrl;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;

class UrlController extends Controller
{
    public function shortUrl()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $siteSetting = SiteSetting::query()
            ->latest('updated_at')
            ->value('short_url');

        $siteSetting = $siteSetting ?? false;

        if (! $user) {
            return redirect('/signin');
        }

        $shortUrlRecord = ShortUrl::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $shortUrlRecord) {
            $slug = md5($user->name . $user->id);

            while (ShortUrl::query()->where('short_url', $slug)->exists()) {
                $slug = md5($user->name . $user->id . rand(1, 9999));
            }

            $shortUrlRecord = ShortUrl::query()->create([
                'user_id' => $user->id,
                'short_url' => $slug,
            ]);
        }

        $slug = $shortUrlRecord->short_url;

        return view('short-url', compact('slug', 'siteSetting'));
    }

    public function updateShortUrl(UpdateShortUrlRequest $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $slug = $request->validated('slug');

        ShortUrl::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['short_url' => $slug]
        );

        return response()->json([
            'success' => true,
            'message' => 'Short URL updated successfully.',
            'slug' => $slug,
        ]);
    }
}
