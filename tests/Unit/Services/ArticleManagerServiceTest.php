<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\ArticleManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class ArticleManagerServiceTest extends TestCase
{
    public function testFetchAndProcessArticles(): void
    {
        $apiName = 'sample-api';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("API '{$apiName}' not supported.");
        $article = Mockery::mock(Article::class);

        $articleManagerService = $this->getMockBuilder(ArticleManagerService::class)
            ->setConstructorArgs([$article])
            ->onlyMethods(['processArticles'])
            ->getMock();

        $articleManagerService->fetchAndProcessArticles($apiName);
    }

    public function testGetArticlesByUserPreferences(): void
    {
        $user = Mockery::mock('alias:App\Models\User');
        $user->id = 1;

        $cacheKey = "user:{$user->id}:preferred_articles";
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $duration, $callback) use ($cacheKey) {
                return $key === $cacheKey && $duration instanceof \DateTime && is_callable($callback);
            })
            ->andReturn(new LengthAwarePaginator([], 0, 10));

        $article = Mockery::mock(Article::class);
        $response = (new ArticleManagerService())->getArticlesByUserPreferences($user);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertArrayHasKey('result', $data);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);

        $this->assertArrayHasKey('current_page', $data['result']);
        $this->assertArrayHasKey('data', $data['result']);
        $this->assertArrayHasKey('first_page_url', $data['result']);
        $this->assertArrayHasKey('last_page', $data['result']);
        $this->assertArrayHasKey('last_page_url', $data['result']);
        $this->assertArrayHasKey('links', $data['result']);
        $this->assertArrayHasKey('next_page_url', $data['result']);
        $this->assertArrayHasKey('path', $data['result']);
        $this->assertArrayHasKey('per_page', $data['result']);
        $this->assertArrayHasKey('prev_page_url', $data['result']);
        $this->assertArrayHasKey('to', $data['result']);
        $this->assertArrayHasKey('total', $data['result']);
    }

    public function testGetArticleDetails(): void
    {
        $articleId = 10;
        $article = Mockery::mock(Article::class);
        $article->shouldReceive('select')->andReturnSelf();
        $article->shouldReceive('with')->andReturnSelf();
        $article->shouldReceive('findOrFail')
            ->with($articleId)
            ->andReturnSelf();

        $article->shouldReceive('jsonSerialize')
            ->andReturn([
                'id' => $articleId,
                'title' => 'Sample Title',
                'content' => 'Sample Content',
                'news_url' => 'http://example.com',
                'source_id' => 1,
                'author_id' => 1,
                'category_id' => 1,
            ]);

        Cache::shouldReceive('remember')
            ->zeroOrMoreTimes()
            ->with(
                "article_{$articleId}",
                Mockery::any(),
                Mockery::on(function ($closure) {
                    return is_callable($closure);
                    ;
                })
            )
            ->andReturn($article);

        $articleManagerService = new ArticleManagerService();
        $response = $articleManagerService->getArticleDetails($articleId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getData(true);

        $this->assertArrayHasKey('title', $data['result']);
        $this->assertArrayHasKey('content', $data['result']);
        $this->assertArrayHasKey('news_url', $data['result']);
    }
}
