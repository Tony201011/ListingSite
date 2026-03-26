<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeletePhotoVerificationPhotoRequest;
use App\Http\Requests\UploadPhotoVerificationRequest;
use App\Models\PhotoVerification;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PhotoVerificationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $latestVerification = null;
        $lastTwoPhotos = [];

        if ($user) {
            $latestVerifications = $user->photoVerification()
                ->whereNull('deleted_at')
                ->latest('created_at')
                ->take(2)
                ->get();

            if ($latestVerifications->isNotEmpty()) {
                $latestVerification = $latestVerifications->first();

                $lastTwoPhotos = $latestVerifications
                    ->map(function ($verification) {
                        $photos = is_array($verification->photos)
                            ? $verification->photos
                            : json_decode($verification->photos, true);

                        if (! is_array($photos)) {
                            $photos = [];
                        }

                        return collect($photos)->map(function ($photo) use ($verification) {
                            $photo['status'] = $verification->status;
                            $photo['admin_note'] = $verification->admin_note;

                            return $photo;
                        });
                    })
                    ->flatten(1)
                    ->take(2)
                    ->values()
                    ->toArray();
            }
        }

        return view('click-here-to-verify', compact(
            'latestVerification',
            'lastTwoPhotos'
        ));
    }

    public function upload(UploadPhotoVerificationRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $countVerification = $user->photoVerification()
                ->whereNull('deleted_at')
                ->count();

            if ($countVerification >= 2) {
                return response()->json([
                    'message' => 'You have upload the maximum number of 2 verification photos. Please contact support for further assistance.',
                ], 403);
            }

            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('s3');

            $baseName = $user->name ?: 'user';
            $username = Str::slug($baseName) . $user->id;

            $uploadedPhotos = [];

            foreach ($request->file('photos', []) as $photo) {
                $originalExtension = strtolower($photo->getClientOriginalExtension() ?: 'jpg');
                $fileName = 'verification_' . $user->id . '_' . Str::uuid() . '.' . $originalExtension;
                $filePath = "verification/{$username}/{$fileName}";

                $uploaded = $disk->putFileAs(
                    "verification/{$username}",
                    $photo,
                    $fileName,
                    [
                        'visibility' => 'public',
                        'ContentType' => $photo->getMimeType(),
                    ]
                );

                if (! $uploaded) {
                    return response()->json([
                        'message' => 'Failed to upload one of the verification photos.',
                    ], 500);
                }

                $uploadedPhotos[] = [
                    'path' => $filePath,
                    'url' => $disk->url($filePath),
                    'name' => $photo->getClientOriginalName(),
                ];
            }

            $verification = PhotoVerification::create([
                'user_id' => $user->id,
                'photos' => $uploadedPhotos,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            return response()->json([
                'message' => 'Verification photos uploaded successfully. Your request is now pending review.',
                'verification' => [
                    'id' => $verification->id,
                    'status' => $verification->status,
                    'submitted_at' => optional($verification->submitted_at)->toDateTimeString(),
                    'photos' => $verification->photos,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Photo verification upload failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $path = $request->validated('path');

        $verifications = $user->photoVerification()
            ->whereNull('deleted_at')
            ->get();

        foreach ($verifications as $verification) {
            $photos = is_array($verification->photos)
                ? $verification->photos
                : json_decode($verification->photos, true);

            if (! is_array($photos)) {
                $photos = [];
            }

            $updatedPhotos = collect($photos)
                ->reject(function ($photo) use ($path) {
                    return isset($photo['path']) && $photo['path'] === $path;
                })
                ->values()
                ->toArray();

            if (count($updatedPhotos) !== count($photos)) {
                $verification->deleted_at = now();
                $verification->save();

                return response()->json([
                    'message' => 'Photo deleted successfully.',
                ]);
            }
        }

        return response()->json([
            'message' => 'Photo not found.',
        ], 404);
    }
}
