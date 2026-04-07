<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        if (str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk(config('media.delivery_disk', 'public'));

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }
}
