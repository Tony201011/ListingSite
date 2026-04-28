<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeleteUserVideo;
use App\Actions\GetActiveProviderProfile;
use App\Actions\GetUserVideos;
use App\Actions\UploadUserVideos;
use App\Http\Controllers\Controller;
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
        private DeleteUserVideo $deleteUserVideo,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', UserVideo::class);

        return view('profile.upload-video');
    }

    public function getVideos(): View
    {
        $this->authorize('viewAny', UserVideo::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $result = $this->getUserVideos->execute($profile);

        if (! $result->isSuccess()) {
            abort($result->status(), $result->message() ?? 'Forbidden');
        }

        return view(
            'profile.my-videos',
            $result->data()
        );
    }

    public function uploadVideos(UploadVideosRequest $request): JsonResponse
    {
        $this->authorize('create', UserVideo::class);

        try {
            $profile = $this->getActiveProviderProfile->execute(Auth::user());
            $result = $this->uploadUserVideos->execute(
                $profile,
                $request->file('videos', [])
            );

            return response()->json($result->toPayload(), $result->status());
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

        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $result = $this->deleteUserVideo->execute($profile, $video);

        return response()->json($result->toPayload(), $result->status());
    }
}
