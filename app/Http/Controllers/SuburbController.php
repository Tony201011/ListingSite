<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuburbSearchRequest;
use App\Models\Postcode;
use Illuminate\Http\JsonResponse;

class SuburbController extends Controller
{
    public function search(SuburbSearchRequest $request): JsonResponse
    {
        $query = $request->validated('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = Postcode::query()
            ->where(function ($q) use ($query) {
                $q->where('suburb', 'LIKE', $query . '%')
                  ->orWhere('postcode', 'LIKE', $query . '%');
            })
            ->orderBy('suburb')
            ->get([
                'id',
                'suburb',
                'state',
                'postcode',
                'latitude',
                'longitude',
            ]);

        return response()->json($results);
    }
}
