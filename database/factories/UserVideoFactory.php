<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserVideo>
 */
class UserVideoFactory extends Factory
{
    protected $model = UserVideo::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'video_path' => 'videos/'.fake()->uuid().'.mp4',
            'original_name' => fake()->word().'.mp4',
        ];
    }
}
