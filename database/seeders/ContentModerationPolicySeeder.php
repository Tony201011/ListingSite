<?php

namespace Database\Seeders;

use App\Models\ContentModerationPolicy;
use Illuminate\Database\Seeder;

class ContentModerationPolicySeeder extends Seeder
{
    public function run(): void
    {
        ContentModerationPolicy::updateOrCreate(
            [
                'title' => 'Content Moderation Policy',
            ],
            [
                'content' => '<h2>Overview</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi.</p><h3>Moderation Standards</h3><p>Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta.</p><h3>Prohibited Content</h3><p>Mauris massa. Vestibulum lacinia arcu eget nulla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p><h3>Enforcement</h3><p>Curabitur sodales ligula in libero. Sed dignissim lacinia nunc. Curabitur tortor. Pellentesque nibh. Aenean quam.</p><h3>Contact</h3><p>In scelerisque sem at dolor. Maecenas mattis. Sed convallis tristique sem. Proin ut ligula vel nunc egestas porttitor.</p>',
                'is_active' => true,
            ],
        );
    }
}
