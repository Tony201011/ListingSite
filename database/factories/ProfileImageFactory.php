<?php

namespace Database\Factories;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProfileImage>
 */
class ProfileImageFactory extends Factory
{
    protected $model = ProfileImage::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'image_path' => 'images/'.fake()->uuid().'.jpg',
            'thumbnail_path' => 'thumbnails/'.fake()->uuid().'.jpg',
            'is_primary' => false,
        ];
    }
}
