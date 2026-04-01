<?php

namespace App\Actions;

use App\Models\BlogPost;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetBlogPostData
{
    public function execute(string $slug): array
    {
        $posts = BlogPost::query()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->values();

        $index = $posts->search(fn (BlogPost $post) => $post->slug === $slug);

        if ($index === false) {
            throw new NotFoundHttpException;
        }

        $current = $posts[$index];
        $previous = $index > 0 ? $posts[$index - 1] : null;
        $next = $index < ($posts->count() - 1) ? $posts[$index + 1] : null;

        return [
            'post' => $this->mapSinglePost($current),
            'previousPost' => $this->mapAdjacentPost($previous),
            'nextPost' => $this->mapAdjacentPost($next),
        ];
    }

    private function mapSinglePost(BlogPost $post): array
    {
        return [
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'author' => $post->author,
            'date' => ($post->published_at ?? $post->created_at)?->format('j F Y'),
            'featured_image' => $post->featured_image,
            'featured_video' => $post->featured_video,
            'content' => (string) $post->content,
        ];
    }

    private function mapAdjacentPost(?BlogPost $post): ?array
    {
        if (! $post) {
            return null;
        }

        return [
            'slug' => $post->slug,
            'title' => $post->title,
        ];
    }
}
