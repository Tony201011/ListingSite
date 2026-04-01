<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetShortUrlPageData;
use App\Actions\UpdateUserShortUrl;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateShortUrlRequest;
use App\Models\ShortUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UrlController extends Controller
{
    public function __construct(
        private GetShortUrlPageData $getShortUrlPageData,
        private UpdateUserShortUrl $updateUserShortUrl
    ) {}

    public function shortUrl(): View|RedirectResponse
    {
        $result = $this->getShortUrlPageData->execute(Auth::user());

        if (isset($result['redirect'])) {
            return redirect($result['redirect']);
        }

        return view('profile.short-url', $result);
    }

    public function updateShortUrl(UpdateShortUrlRequest $request): JsonResponse
    {
        $this->authorize('create', ShortUrl::class);

        $result = $this->updateUserShortUrl->execute(
            Auth::user(),
            $request->validated('slug')
        );

        return response()->json($result['data'], $result['status']);
    }
}
