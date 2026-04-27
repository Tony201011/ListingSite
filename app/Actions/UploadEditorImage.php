<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class UploadEditorImage
{
    public function execute(?User $user, UploadedFile $image): ActionResult
    {
        if (! $user) {
            return ActionResult::domainError('Unauthenticated.', status: 401);
        }

        $disk = Storage::disk(config('media.upload_disk'));

        $baseName = trim((string) ($user->name ?: 'user'));
        $slug = Str::slug($baseName);
        if ($slug === '') {
            $slug = 'user';
        }
        $username = $slug.$user->id;

        $extension = strtolower($image->extension() ?: $image->getClientOriginalExtension() ?: 'jpg');
        $fileName = 'editor_'.$user->id.'_'.Str::uuid().'.'.$extension;
        $storagePath = "editor-images/{$username}/{$fileName}";

        try {
            $uploaded = $disk->putFileAs(
                "editor-images/{$username}",
                $image,
                $fileName,
                [
                    'visibility' => 'public',
                    'ContentType' => $image->getMimeType() ?: 'application/octet-stream',
                ]
            );

            if (! $uploaded) {
                return ActionResult::infrastructureFailure('Failed to upload image.');
            }

            return ActionResult::success(
                ['url' => $disk->url($storagePath)],
                'Image uploaded successfully.'
            );
        } catch (Throwable $e) {
            $disk->delete($storagePath);

            return ActionResult::infrastructureFailure('Failed to upload image.');
        }
    }
}
