<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeletePhotoVerificationPhoto;
use App\Actions\GetActiveProviderProfile;
use App\Actions\GetPhotoVerificationPageData;
use App\Actions\UploadPhotoVerificationPhotos;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeletePhotoVerificationPhotoRequest;
use App\Http\Requests\UploadPhotoVerificationRequest;
use App\Models\PhotoVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PhotoVerificationController extends Controller
{
    public function __construct(
        private GetPhotoVerificationPageData $getPhotoVerificationPageData,
        private UploadPhotoVerificationPhotos $uploadPhotoVerificationPhotos,
        private DeletePhotoVerificationPhoto $deletePhotoVerificationPhoto,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function index(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        return view(
            'profile.verify-photo',
            $this->getPhotoVerificationPageData->execute($profile)
        );
    }

    public function upload(UploadPhotoVerificationRequest $request): JsonResponse
    {
        $this->authorize('create', PhotoVerification::class);

        try {
            $profile = $this->getActiveProviderProfile->execute(Auth::user());
            $result = $this->uploadPhotoVerificationPhotos->execute(
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
                    : 'Something went wrong while uploading verification photos.',
            ], 500);
        }
    }

    public function deletePhoto(DeletePhotoVerificationPhotoRequest $request): JsonResponse
    {
        $this->authorize('deletePhoto', PhotoVerification::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $result = $this->deletePhotoVerificationPhoto->execute(
            $profile,
            $request->validated('path')
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
