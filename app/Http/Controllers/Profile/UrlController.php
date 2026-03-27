<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

use App\Actions\GetShortUrlPageData;
use App\Actions\UpdateUserShortUrl;
use App\Http\Requests\UpdateShortUrlRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UrlController extends Controller
{
    public function __construct(
        private GetShortUrlPageData $getShortUrlPageData,
        private UpdateUserShortUrl $updateUserShortUrl
    ) {
    }

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
        $result = $this->updateUserShortUrl->execute(
            Auth::user(),
            $request->validated('slug')
        );

        return response()->json($result['data'], $result['status']);
    }
}
