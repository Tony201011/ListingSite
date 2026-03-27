<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use App\Actions\DeleteProfilePhoto;
use App\Actions\GetUserPhotos;
use App\Actions\SetPrimaryProfilePhoto;
use App\Actions\UploadUserPhotos;
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
        private DeleteProfilePhoto $deleteProfilePhoto
    ) {
    }

    public function index(): View
    {
        return view('profile.add-photo');
    }

    public function getPhotos(): View
    {
        return view('profile.photos', $this->getUserPhotos->execute(Auth::id()));
    }

    public function uploadPhotos(UploadPhotosRequest $request): JsonResponse
    {
        try {
            $result = $this->uploadUserPhotos->execute(
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
                    : 'Something went wrong while uploading photos.',
            ], 500);
        }
    }

    public function setCover(ProfileImage $photo): JsonResponse
    {
        $result = $this->setPrimaryProfilePhoto->execute(Auth::user(), $photo);

        return response()->json($result['data'], $result['status']);
    }

    public function destroy(ProfileImage $photo): JsonResponse
    {
        $result = $this->deleteProfilePhoto->execute(Auth::user(), $photo);

        return response()->json($result['data'], $result['status']);
    }
}
