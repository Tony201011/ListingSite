<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;



class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'The Evolution of Digital Intimacy Platforms',
                'slug' => 'the-evolution-of-digital-intimacy-platforms',
                'excerpt' => 'How technology and trust mechanisms are reshaping modern companion and lifestyle directories online.',
                'content' => '<p>Digital lifestyle platforms have evolved from basic directories into full ecosystems focused on trust, safety, and user experience.</p><p>From profile verification to privacy-first communication, users now expect professional standards and transparent policies before engaging.</p><p>As the market matures, providers and platforms that invest in quality content and clear communication build stronger long-term relationships with their audiences.</p>',
                'author' => 'Alice',
                'published_at' => now()->subDays(20),
                'is_active' => true,
            ],
            [
                'title' => 'Building Better User Trust With Verified Profiles',
                'slug' => 'building-better-user-trust-with-verified-profiles',
                'excerpt' => 'Verification workflows can significantly increase quality and confidence for both providers and visitors.',
                'content' => '<p>Trust is one of the biggest conversion drivers in service marketplaces. Verified identities and media authenticity checks reduce friction and hesitation.</p><p>When platforms clearly explain what is verified and how, users are more likely to engage responsibly and confidently.</p><p>Regular reviews of verification standards help maintain credibility as fraud patterns and user expectations change over time.</p>',
                'author' => 'Alice',
                'published_at' => now()->subDays(14),
                'is_active' => true,
            ],
            [
                'title' => 'Content Strategies That Increase Engagement',
                'slug' => 'content-strategies-that-increase-engagement',
                'excerpt' => 'Practical editorial tips for publishing blog content that users actually read, save, and share.',
                'content' => '<p>Great content starts with clear audience intent. Educational guides, safety advice, and transparent updates consistently outperform generic promotional posts.</p><p>Use concise headlines, meaningful excerpts, and scannable sections so readers can find value quickly on mobile devices.</p><p>Track post-level engagement signals and repeat what works: relevance, clarity, and consistency always win.</p>',
                'author' => 'Alice',
                'published_at' => now()->subDays(7),
                'is_active' => true,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                $post,
            );
        }
    }
}
