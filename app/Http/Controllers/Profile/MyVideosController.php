<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Actions\DeleteUserVideo;
use App\Actions\GetUserVideos;
use App\Actions\UploadUserVideos;
use App\Http\Requests\UploadVideosRequest;
use App\Models\UserVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class MyVideosController extends Controller
{
    public function __construct(
        private GetUserVideos $getUserVideos,
        private UploadUserVideos $uploadUserVideos,
        private DeleteUserVideo $deleteUserVideo
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', UserVideo::class);

        return view('profile.upload-video');
    }

    public function getVideos(): View
    {
        $this->authorize('viewAny', UserVideo::class);

        return view(
            'profile.my-videos',
            $this->getUserVideos->execute(Auth::user())
        );
    }

    public function uploadVideos(UploadVideosRequest $request): JsonResponse
    {
        $this->authorize('create', UserVideo::class);

        try {
            $result = $this->uploadUserVideos->execute(
                Auth::user(),
                $request->file('videos', [])
            );

            return response()->json($result['data'], $result['status']);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Upload failed.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while uploading videos.',
            ], 500);
        }
    }

    public function destroy(UserVideo $video): JsonResponse
    {
        $this->authorize('delete', $video);

        $result = $this->deleteUserVideo->execute(Auth::user(), $video);

        return response()->json($result['data'], $result['status']);
    }
}
