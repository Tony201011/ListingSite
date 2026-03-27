<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

use App\Actions\SearchSuburbs;
use App\Http\Requests\SuburbSearchRequest;
use Illuminate\Http\JsonResponse;

class SuburbController extends Controller
{
    public function __construct(
        private SearchSuburbs $searchSuburbs
    ) {
    }

    public function search(SuburbSearchRequest $request): JsonResponse
    {
        return response()->json(
            $this->searchSuburbs->execute(
                $request->validated('q')
            )
        );
    }
}
