<?php

namespace App\Services;

use App\Factories\ArticleApiFactory;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ArticleManagerService
{
    /**
     * Fetch articles from the specified API and process them.
     *
     * @param string $apiName
     * @return void
     */
    public function fetchAndProcessArticles(string $apiName): void
    {
        [$apiInstance, $adapter] = ArticleApiFactory::make($apiName);

        $articles = $apiInstance->fetchArticles();
        $this->processArticles($articles, $adapter);
    }

    /**
     * Handle the transformation and storage of articles.
     *
     * @param array $articles
     * @param object $adapter
     * @return void
     */
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

    public function getArticlesByUserPreferences(User $user, int $perPage = 10): JsonResponse
    {
        $cacheKey = "user:{$user->id}:preferred_articles";

        $articles = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user, $perPage) {
            $preferenceIds = [
                'authors' => $user->preferredAuthors()->pluck('authors.id')->toArray(),
                'sources' => $user->preferredSources()->pluck('sources.id')->toArray(),
                'categories' => $user->preferredCategories()->pluck('categories.id')->toArray(),
            ];
    
            return Article::query()
                ->when(!empty($preferenceIds['authors']), fn($query) => $query->whereIn('author_id', $preferenceIds['authors']))
                ->when(!empty($preferenceIds['sources']), fn($query) => $query->whereIn('source_id', $preferenceIds['sources']))
                ->when(!empty($preferenceIds['categories']), fn($query) => $query->whereHas('category', fn($q) => $q->whereIn('categories.id', $preferenceIds['categories'])))
                ->select(['id', 'title', 'content', 'author_id', 'source_id', 'category_id', 'news_url'])
                ->with(['author:id,name', 'source:id,name', 'category:id,name'])
                ->paginate($perPage);
        });

        return response()->json($articles);
    }
}
