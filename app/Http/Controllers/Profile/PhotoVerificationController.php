<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use App\Actions\DeletePhotoVerificationPhoto;
use App\Actions\GetPhotoVerificationPageData;
use App\Actions\UploadPhotoVerificationPhotos;
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
        private DeletePhotoVerificationPhoto $deletePhotoVerificationPhoto
    ) {
    }

    public function index(): View
    {
        return view(
            'profile.verify-photo',
            $this->getPhotoVerificationPageData->execute(Auth::user())
        );
    }

    public function upload(UploadPhotoVerificationRequest $request): JsonResponse
    {
        $this->authorize('create', PhotoVerification::class);

        try {
            $result = $this->uploadPhotoVerificationPhotos->execute(
                Auth::user(),
                $request->file('photos', [])
            );

            return response()->json($result['data'], $result['status']);
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

        $result = $this->deletePhotoVerificationPhoto->execute(
            Auth::user(),
            $request->validated('path')
        );

        return response()->json($result['data'], $result['status']);
    }
}
