<?php

namespace App\Http\Controllers;

use App\Models\Postcode;
use Illuminate\Http\Request;

class SuburbController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

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
