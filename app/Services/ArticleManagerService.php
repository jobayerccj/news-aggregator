<?php

namespace App\Services;

use App\Factories\ArticleApiFactory;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;

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
}
