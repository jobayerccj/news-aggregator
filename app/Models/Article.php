<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Article extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['title', 'content', 'author_id', 'source_id', 'category_id', 'news_url'];
    protected $with = ['author', 'source', 'category'];

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'author' => $this->author->name ?? null,
            'source' => $this->source->name ?? null,
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeFilterByKeyword($query, $keyword)
    {
        if ($keyword) {
            $query->where('title', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%");
        }
    }

    public function scopeFilterByDate($query, $date)
    {
        if ($date) {
            $query->whereDate('created_at', $date);
        }
    }

    public function scopeFilterByCategory($query, $categoryName)
    {
        if ($categoryName) {
            $query->whereHas('category', function ($q) use ($categoryName) {
                $q->where('categories.name', 'like', "%{$categoryName}%");
            });
        }
    }

    public function scopeFilterBySource($query, $sourceName)
    {
        if ($sourceName) {
            $query->whereHas('source', function ($q) use ($sourceName) {
                $q->where('sources.name', 'like', "%{$sourceName}%");
            });
        }
    }
}
