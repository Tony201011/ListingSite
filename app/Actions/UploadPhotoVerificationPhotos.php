<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\PhotoVerification;
use App\Models\ProviderProfile;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class UploadPhotoVerificationPhotos
{
    public function execute(ProviderProfile $profile, array $photos): ActionResult
    {
        $countVerification = $profile->photoVerification()
            ->whereNull('deleted_at')
            ->count();

        if ($countVerification >= 2) {
            return ActionResult::domainError(
                'You have upload the maximum number of 2 verification photos. Please contact support for further assistance.',
                status: 403
            );
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.upload_disk'));

        $baseName = $profile->name ?: 'user';
        $username = Str::slug($baseName).$profile->id;

        $uploadedPhotos = [];
        $storedPaths = [];

        try {
            foreach ($photos as $photo) {
                if (! $photo instanceof UploadedFile) {
                    continue;
                }

                $originalExtension = strtolower($photo->getClientOriginalExtension() ?: 'jpg');
                $fileName = 'verification_'.$profile->id.'_'.Str::uuid().'.'.$originalExtension;
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
                    foreach ($storedPaths as $storedPath) {
                        $disk->delete($storedPath);
                    }

                    return ActionResult::infrastructureFailure('Failed to upload one of the verification photos.');
                }

                $storedPaths[] = $filePath;

                $uploadedPhotos[] = [
                    'path' => $filePath,
                    'url' => $disk->url($filePath),
                    'name' => $photo->getClientOriginalName(),
                ];
            }

            $verification = PhotoVerification::create([
                'user_id' => $profile->user_id,
                'provider_profile_id' => $profile->id,
                'photos' => $uploadedPhotos,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            return ActionResult::success([
                'verification' => [
                    'id' => $verification->id,
                    'status' => $verification->status,
                    'submitted_at' => optional($verification->submitted_at)->toDateTimeString(),
                    'photos' => $verification->photos,
                ],
            ], 'Verification photos uploaded successfully. Your request is now pending review.');
        } catch (Throwable $e) {
            foreach ($storedPaths as $storedPath) {
                $disk->delete($storedPath);
            }

            throw $e;
        }
    }
}
