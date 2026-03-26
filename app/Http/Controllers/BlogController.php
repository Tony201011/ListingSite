<?php

namespace App\Http\Controllers;

use App\Actions\GetBlogIndexData;
use App\Actions\GetBlogPostData;
use App\Http\Requests\LoadMoreBlogPostsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(
        private GetBlogIndexData $getBlogIndexData,
        private GetBlogPostData $getBlogPostData
    ) {
    }

    public function index(): View
    {
        $data = $this->getBlogIndexData->execute();

        return view('blog', $data);
    }

    public function loadMore(LoadMoreBlogPostsRequest $request): JsonResponse
    {
        $data = $this->getBlogIndexData->execute(
            (int) $request->validated('page', 1)
        );

        return response()->json([
            'posts' => $data['posts'],
            'hasMore' => $data['hasMore'],
            'nextPage' => $data['nextPage'],
        ]);
    }

    public function show(string $slug): View
    {
        $data = $this->getBlogPostData->execute($slug);

        return view('blog-show', $data);
    }
}
