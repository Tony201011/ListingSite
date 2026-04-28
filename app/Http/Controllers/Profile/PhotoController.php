<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeleteProfilePhoto;
use App\Actions\GetActiveProviderProfile;
use App\Actions\GetUserPhotos;
use App\Actions\SetPrimaryProfilePhoto;
use App\Actions\UploadEditorImage;
use App\Actions\UploadUserPhotos;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadEditorImageRequest;
use App\Http\Requests\UploadPhotosRequest;
use App\Models\ProfileImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PhotoController extends Controller
{
    public function __construct(
        private GetUserPhotos $getUserPhotos,
        private UploadUserPhotos $uploadUserPhotos,
        private SetPrimaryProfilePhoto $setPrimaryProfilePhoto,
        private DeleteProfilePhoto $deleteProfilePhoto,
        private UploadEditorImage $uploadEditorImage,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ProfileImage::class);

        return view('profile.add-photo');
    }

    public function getPhotos(): View
    {
        $this->authorize('viewAny', ProfileImage::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $result = $this->getUserPhotos->execute($profile);

        if (! $result->isSuccess()) {
            abort($result->status(), $result->message() ?? 'Forbidden');
        }

        return view(
            'profile.photos',
            $result->data()
        );
    }

    public function uploadPhotos(UploadPhotosRequest $request): JsonResponse
    {
        $this->authorize('create', ProfileImage::class);

        try {
            $profile = $this->getActiveProviderProfile->execute(Auth::user());
            $result = $this->uploadUserPhotos->execute(
                $profile,
                $request->file('photos', [])
            );

            return response()->json($result->toPayload(), $result->status());
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Upload failed.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while uploading photos.',
            ], 500);
        }
    }

    public function uploadEditorImage(UploadEditorImageRequest $request): JsonResponse
    {
        $this->authorize('create', ProfileImage::class);

        try {
            $result = $this->uploadEditorImage->execute(
                Auth::user(),
                $request->file('image')
            );

            return response()->json($result->toPayload(), $result->status());
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Upload failed.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while uploading the image.',
            ], 500);
        }
    }

    public function setCover(ProfileImage $photo): JsonResponse
    {
        $this->authorize('update', $photo);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $result = $this->setPrimaryProfilePhoto->execute($profile, $photo);

        return response()->json($result->toPayload(), $result->status());
    }

    public function destroy(ProfileImage $photo): JsonResponse
    {
        $this->authorize('delete', $photo);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $result = $this->deleteProfilePhoto->execute($profile, $photo);

        return response()->json($result->toPayload(), $result->status());
    }
}
