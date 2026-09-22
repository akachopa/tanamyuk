<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->ok($this->publishedQuery($request)->get());
    }

    public function faqs(Request $request): JsonResponse
    {
        $request->merge(['type' => 'faq']);

        return $this->ok($this->publishedQuery($request)->get());
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->with(['category', 'commodities'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return $this->ok($article);
    }

    private function publishedQuery(Request $request)
    {
        return Article::query()
            ->with('category')
            ->where('status', 'published')
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->whereHas('category', fn ($category) => $category->where('type', $request->string('type')));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)->orWhere('summary', 'like', $term);
                });
            })
            ->orderByDesc('published_at');
    }
}
