<?php

namespace Tests\Unit;

use App\Models\ProfileImage;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MediaConfigPortabilityTest extends TestCase
{
    public function test_profile_image_urls_use_configured_delivery_disk(): void
    {
        Config::set('filesystems.disks.media_test', [
            'driver' => 'local',
            'root' => storage_path('app/media-test'),
            'url' => 'https://cdn.example.test',
        ]);
        Config::set('media.delivery_disk', 'media_test');

        $image = new ProfileImage([
            'image_path' => 'images/user/photo.jpg',
            'thumbnail_path' => 'thumbnails/user/photo_thumb.jpg',
        ]);

        $this->assertSame('https://cdn.example.test/images/user/photo.jpg', $image->image_url);
        $this->assertSame('https://cdn.example.test/thumbnails/user/photo_thumb.jpg', $image->thumbnail_url);
    }

    public function test_user_video_url_uses_configured_delivery_disk(): void
    {
        Config::set('filesystems.disks.media_test', [
            'driver' => 'local',
            'root' => storage_path('app/media-test'),
            'url' => 'https://media.example.test',
        ]);
        Config::set('media.delivery_disk', 'media_test');

        $video = new UserVideo([
            'video_path' => 'videos/user/video.mp4',
        ]);

        $this->assertSame('https://media.example.test/videos/user/video.mp4', $video->video_url);
    }

    public function test_avatar_url_uses_configured_avatar_disk(): void
    {
        Config::set('filesystems.disks.avatar_test', [
            'driver' => 'local',
            'root' => storage_path('app/avatar-test'),
            'url' => 'https://avatars.example.test',
        ]);
        Config::set('media.avatar_disk', 'avatar_test');

        $user = new User([
            'profile_image' => 'avatars/user-avatar.jpg',
        ]);

        $this->assertSame('https://avatars.example.test/avatars/user-avatar.jpg', $user->getFilamentAvatarUrl());
    }
}
