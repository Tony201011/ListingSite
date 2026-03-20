<?php

namespace App\Http\Controllers;

use App\Models\ProfileImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PhotoController extends Controller
{
    public function index(Request $request)
    {
        return view('add-photo');
    }

    public function getPhotos(Request $request)
    {
        $photos = ProfileImage::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('photos', compact('photos'));
    }

    public function uploadPhotos(Request $request)
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        $username = Str::slug($user->name.$user->id ?? 'user-' . $user->id);
        $uploadedPhotos = [];

        foreach ($request->file('photos') as $photo) {
            $hasPrimary = ProfileImage::where('user_id', $user->id)->where('is_primary', true)->exists();
            $isPrimary = ! $hasPrimary && empty($uploadedPhotos);
            $extension = strtolower($photo->getClientOriginalExtension()) ?: 'jpg';
            $fileName = 'profile_' . $user->id . '_' . Str::uuid() . '.' . $extension;

            $imagePath = "images/{$username}/{$fileName}";
            $thumbnailPath = "thumbnails/{$username}/{$fileName}";

            // Save original image
            Storage::disk('s3')->putFileAs(
                "images/{$username}",
                $photo,
                $fileName,
                ['visibility' => 'public']
            );

            // Create fixed-size thumbnail
            // $thumbnail = Image::read($photo->getRealPath())
            //     ->cover(400, 400)   // all thumbnails same size
            //     ->toJpeg(85);

            // Storage::disk('s3')->put(
            //     $thumbnailPath,
            //     (string) $thumbnail,
            //     ['visibility' => 'public', 'ContentType' => 'image/jpeg']
            // );

            $profileImage = ProfileImage::create([
                'user_id' => $user->id,
                'image_path' => $imagePath,
                'thumbnail_path' => $imagePath,
                'is_primary' => false,
            ]);

            $uploadedPhotos[] = [
                'id' => $profileImage->id,
                'image_path' => $profileImage->image_path,
                'thumbnail_path' => $profileImage->thumbnail_path,
                'image_url' => Storage::disk('s3')->url($profileImage->image_path),
                'thumbnail_url' => Storage::disk('s3')->url($profileImage->thumbnail_path),
                'is_primary' =>$isPrimary,
            ];
        }

        return response()->json([
            'message' => 'Photos uploaded successfully.',
            'photos' => $uploadedPhotos,
        ]);
    }

    public function setCover(ProfileImage $photo)
{
    $user = Auth::user();

    if (! $user || $photo->user_id !== $user->id) {
        return response()->json([
            'message' => 'Unauthorized.'
        ], 403);
    }

    ProfileImage::where('user_id', $user->id)->update([
        'is_primary' => false
    ]);

    $photo->update([
        'is_primary' => true
    ]);

    return response()->json([
        'message' => 'Cover photo updated successfully.'
    ]);
}

public function destroy(ProfileImage $photo)
{
    $user = Auth::user();

    if (! $user || $photo->user_id !== $user->id) {
        return response()->json([
            'message' => 'Unauthorized.'
        ], 403);
    }

    if ($photo->image_path && Storage::disk('s3')->exists($photo->image_path)) {
        Storage::disk('s3')->delete($photo->image_path);
    }

    if ($photo->thumbnail_path && Storage::disk('s3')->exists($photo->thumbnail_path)) {
        Storage::disk('s3')->delete($photo->thumbnail_path);
    }

    $wasPrimary = $photo->is_primary;

    $photo->delete();

    if ($wasPrimary) {
        $nextPhoto = ProfileImage::where('user_id', $user->id)->latest()->first();

        if ($nextPhoto) {
            $nextPhoto->update([
                'is_primary' => true
            ]);
        }
    }

    return response()->json([
        'message' => 'Photo deleted successfully.'
    ]);
}
}
