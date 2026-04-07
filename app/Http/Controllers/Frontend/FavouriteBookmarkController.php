<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FavouriteBookmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavouriteBookmarkController extends Controller
{
    public function __construct(private FavouriteBookmarkService $service) {}

    public function toggleFavourite(Request $request, string $slug): JsonResponse
    {
        if (! $this->service->slugExists($slug)) {
            return response()->json(['error' => 'Profile not found.'], 404);
        }

        $active = $this->service->toggleFavourite($slug);

        return response()->json(['active' => $active]);
    }

    public function toggleBookmark(Request $request, string $slug): JsonResponse
    {
        if (! $this->service->slugExists($slug)) {
            return response()->json(['error' => 'Profile not found.'], 404);
        }

        $active = $this->service->toggleBookmark($slug);

        return response()->json(['active' => $active]);
    }
}
