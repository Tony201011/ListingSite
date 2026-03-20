<?php

namespace App\Http\Controllers;

use App\Models\PhotoVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PhotoVerificationController extends Controller
{
    public function index()
    {
        $latestVerification = PhotoVerification::where('user_id', Auth::id())
            ->latest()
            ->first();

        return view('verify-profile-photos', compact('latestVerification'));
    }

    public function upload(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'photos' => ['required', 'array', 'min:1', 'max:5'],
                'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

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

                if (!$uploaded) {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
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
}
