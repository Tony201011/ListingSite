<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoadMoreBlogPostsRequest;
use App\Models\BlogPost;

class BlogController extends Controller
{
    private const PER_PAGE = 5;

    public function index()
    {
        $paginator = BlogPost::query()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        $posts = collect($paginator->items())
            ->map(function (BlogPost $post) {
                return [
                    'slug' => $post->slug,
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'author' => $post->author,
                    'date' => ($post->published_at ?? $post->created_at)?->format('j F Y'),
                ];
            })
            ->values()
            ->all();

        return view('blog', [
            'posts' => $posts,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->currentPage() + 1,
            'lazyLoadUrl' => route('blog.load-more'),
        ]);
    }

    public function loadMore(LoadMoreBlogPostsRequest $request)
    {
        $page = (int) $request->validated('page', 1);

        $paginator = BlogPost::query()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page);

        $posts = collect($paginator->items())
            ->map(function (BlogPost $post) {
                return [
                    'slug' => $post->slug,
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'author' => $post->author,
                    'date' => ($post->published_at ?? $post->created_at)?->format('j F Y'),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'posts' => $posts,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->currentPage() + 1,
        ]);
    }

    public function show(string $slug)
    {
        $posts = BlogPost::query()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->values();

        $index = $posts->search(fn (BlogPost $post) => $post->slug === $slug);

        if ($index === false) {
            abort(404);
        }

        $current = $posts[$index];

        $post = [
            'slug' => $current->slug,
            'title' => $current->title,
            'excerpt' => $current->excerpt,
            'author' => $current->author,
            'date' => ($current->published_at ?? $current->created_at)?->format('j F Y'),
            'featured_image' => $current->featured_image,
            'featured_video' => $current->featured_video,
            'content' => (string) $current->content,
        ];

        $previous = $index > 0 ? $posts[$index - 1] : null;
        $next = $index < ($posts->count() - 1) ? $posts[$index + 1] : null;

        $previousPost = $previous ? [
            'slug' => $previous->slug,
            'title' => $previous->title,
        ] : null;

        $nextPost = $next ? [
            'slug' => $next->slug,
            'title' => $next->title,
        ] : null;

        return view('blog-show', [
            'post' => $post,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
        ]);
    }
}
