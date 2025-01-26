<?php

namespace App\Services;

use App\Factories\ArticleApiFactory;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ArticleManagerService
{
    use ApiResponse;

    public function fetchAndProcessArticles(string $apiName): void
    {
        [$apiInstance, $adapter] = ArticleApiFactory::make($apiName);

        $articles = $apiInstance->fetchArticles();
        $this->processArticles($articles, $adapter);
    }

    public function getArticlesByUserPreferences(User $user, int $perPage = 10): JsonResponse
    {
        $cacheKey = "user:{$user->id}:preferred_articles";
        $articles = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($user, $perPage) {
            $preferenceIds = [
                'authors' => $user->preferredAuthors()->pluck('authors.id')->toArray(),
                'sources' => $user->preferredSources()->pluck('sources.id')->toArray(),
                'categories' => $user->preferredCategories()->pluck('categories.id')->toArray(),
            ];

            return Article::query()
                ->when(!empty($preferenceIds['authors']), fn ($query) => $query->whereIn('author_id', $preferenceIds['authors']))
                ->when(!empty($preferenceIds['sources']), fn ($query) => $query->whereIn('source_id', $preferenceIds['sources']))
                ->when(!empty($preferenceIds['categories']), fn ($query) => $query->whereHas('category', fn ($q) => $q->whereIn('categories.id', $preferenceIds['categories'])))
                ->select(['id', 'title', 'content', 'author_id', 'source_id', 'category_id', 'news_url'])
                ->with(['author:id,name', 'source:id,name', 'category:id,name'])
                ->paginate($perPage);
        });

        return $this->successResponse($articles);
    }

    public function searchArticles(array $filters, int $perPage = 10): JsonResponse
    {
        $articles = Article::select(['id', 'title', 'content', 'created_at', 'author_id', 'source_id', 'category_id'])
            ->with([
                'author:id,name',
                'source:id,name',
                'category:id,name',
            ])
            ->filterByKeyword($filters['keyword'] ?? null)
            ->filterByDate($filters['date'] ?? null)
            ->filterByCategory($filters['category'] ?? null)
            ->filterBySource($filters['source'] ?? null)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($articles);
    }

    public function getArticleDetails(int $articleId): JsonResponse
    {
        $cacheKey = "article_{$articleId}";

        $articleDetails = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($articleId) {
            return Article::select('id', 'title', 'content', 'news_url', 'source_id', 'author_id', 'category_id')
                ->with([
                    'author:id,name',
                    'source:id,name',
                    'category:id,name',
                ])
                ->findOrFail($articleId);
        });

        return $this->successResponse($articleDetails);
    }

    protected function processArticles(array $articles, object $adapter): void
    {
        foreach ($articles as $apiArticle) {
            $transformed = $adapter->transform($apiArticle)->toArray();
            $author = $this->handleAuthor($transformed['author']);
            $source = $this->handleSource($transformed['source']);
            $category = $this->handleCategory($transformed['category']);
            $this->saveArticle($transformed, $author->id, $source->id, $category->id);
        }
    }

    protected function handleAuthor(string $authorName): Author
    {
        return Author::firstOrCreate(['name' => $authorName]);
    }

    protected function handleSource(string $sourceName): Source
    {
        return Source::firstOrCreate(['name' => $sourceName]);
    }

    protected function handleCategory(string $categoryName): Category
    {
        return Category::firstOrCreate(['name' => $categoryName]);
    }

    protected function saveArticle(array $data, int $authorId, int $sourceId, int $categoryId): void
    {
        Article::updateOrCreate(
            ['title' => $data['title']],
            [
                'news_url' => $data['news_url'],
                'content' => $data['content'],
                'author_id' => $authorId,
                'source_id' => $sourceId,
                'category_id' => $categoryId,
            ]
        );
    }
}
