<?php

namespace Tests\Unit\Models;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    public function testAuthorRelationship(): void
    {
        $article = new Article();
        $relationship = $article->author();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertInstanceOf(Author::class, $relationship->getRelated());
        $this->assertEquals('author_id', $relationship->getForeignKeyName());
    }

    public function testSourceRelationship(): void
    {
        $article = new Article();
        $relationship = $article->source();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertInstanceOf(Source::class, $relationship->getRelated());
        $this->assertEquals('source_id', $relationship->getForeignKeyName());
    }

    public function testCategoryRelationship(): void
    {
        $article = new Article();
        $relationship = $article->category();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertInstanceOf(Category::class, $relationship->getRelated());
        $this->assertEquals('category_id', $relationship->getForeignKeyName());
    }

    public function testToSearchableArrayReturnsCorrectArray()
    {
        $author = Author::factory()->create(['name' => 'John Doe']);
        $source = Source::factory()->create(['name' => 'Example Source']);

        /** @var Article */
        $article = Article::factory()->create([
            'title' => 'Test Article',
            'content' => 'Test Content',
            'author_id' => $author->getAttribute('id'),
            'source_id' => $source->getAttribute('id'),
        ]);

        $searchableArray = $article->toSearchableArray();

        $this->assertEquals([
            'id' => $article->getAttribute('id'),
            'title' => 'Test Article',
            'content' => 'Test Content',
            'author' => 'John Doe',
            'source' => 'Example Source',
        ], $searchableArray);
    }

    public function testToSearchableArrayHandlesNullAuthorAndSource()
    {
        /** @var Article */
        $article = Article::factory()->create([
            'title' => 'Test Article',
            'content' => 'Test Content',
            'author_id' => null,
            'source_id' => null,
        ]);

        $searchableArray = $article->toSearchableArray();

        $this->assertEquals([
            'id' => $article->getAttribute('id'),
            'title' => 'Test Article',
            'content' => 'Test Content',
            'author' => null,
            'source' => null,
        ], $searchableArray);
    }

    public function testFilterByKeywordFiltersWhenKeywordIsProvided()
    {
        $keyword = 'test';
        $query = Article::filterByKeyword($keyword);

        $this->assertInstanceOf(Builder::class, $query);
        $this->assertStringContainsString('where ("title" like ? or "content" like ?)', $query->toSql());
        $this->assertEquals(["%{$keyword}%", "%{$keyword}%"], $query->getBindings());
    }

    public function testFilterByKeywordDoesNotFilterWhenKeywordIsNotProvided()
    {
        $keyword = null;
        $query = Article::query();
        $originalSql = $query->toSql();
        $originalBindings = $query->getBindings();

        $query = Article::filterByKeyword($keyword);

        $this->assertInstanceOf(Builder::class, $query);
        $this->assertEquals($originalSql, $query->toSql());
        $this->assertEquals($originalBindings, $query->getBindings());
    }
}
