<?php

namespace App\Actions;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;

class GetBlogIndexData
{
    private const PER_PAGE = 5;

    public function execute(int $page = 1): array
    {
        $paginator = BlogPost::query()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page);

        $posts = collect($paginator->items())
            ->map(fn (BlogPost $post) => $this->mapListPost($post))
            ->values()
            ->all();

        return [
            'posts' => $posts,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->currentPage() + 1,
            'lazyLoadUrl' => route('blog.load-more'),
        ];
    }

    private function mapListPost(BlogPost $post): array
    {
        return [
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'author' => $post->author,
            'date' => ($post->published_at ?? $post->created_at)?->format('j F Y'),
            'image' => $post->featured_image ? Storage::url($post->featured_image) : null,
        ];
    }
}
