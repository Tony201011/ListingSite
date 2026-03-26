<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadVideosRequest;
use App\Models\UserVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MyVideosController extends Controller
{
    public function index()
    {
        return view('upload-video');
    }

    public function getVideos()
    {
        $videos = UserVideo::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('my-videos', compact('videos'));
    }

    public function uploadVideos(UploadVideosRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $disk = Storage::disk('s3');
            $baseName = $user->name ?: 'user';
            $username = Str::slug($baseName) . $user->id;

            $uploadedVideos = [];

            foreach ($request->file('videos', []) as $video) {
                $originalExtension = strtolower($video->getClientOriginalExtension() ?: 'mp4');
                $baseFileName = 'profile_video_' . $user->id . '_' . Str::uuid();
                $videoFileName = $baseFileName . '.' . $originalExtension;
                $videoPath = "videos/{$username}/{$videoFileName}";

                $uploaded = $disk->putFileAs(
                    "videos/{$username}",
                    $video,
                    $videoFileName,
                    [
                        'visibility' => 'public',
                        'ContentType' => $video->getMimeType(),
                    ]
                );

                if (! $uploaded) {
                    return response()->json([
                        'message' => 'Failed to upload one of the videos to storage.',
                    ], 500);
                }

                $profileVideo = UserVideo::create([
                    'user_id' => $user->id,
                    'video_path' => $videoPath,
                    'original_name' => $video->getClientOriginalName(),
                ]);

                $uploadedVideos[] = [
                    'id' => $profileVideo->id,
                    'video_path' => $profileVideo->video_path,
                    'video_url' => $disk->url($profileVideo->video_path),
                    'original_name' => $profileVideo->original_name,
                    'created_at' => $profileVideo->created_at,
                ];
            }

            return response()->json([
                'message' => 'Videos uploaded successfully.',
                'videos' => $uploadedVideos,
            ]);
        } catch (Throwable $e) {
            Log::error('Multiple video upload failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $video->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $disk = Storage::disk('s3');

        if ($video->video_path) {
            $disk->delete($video->video_path);
        }

        $video->delete();

        return response()->json([
            'message' => 'Video deleted successfully.',
        ]);
    }
}
