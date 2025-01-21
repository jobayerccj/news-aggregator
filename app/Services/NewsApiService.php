<?php

namespace App\Services;

use App\Contracts\ArticleApiInterface;
use Illuminate\Support\Facades\Http;

class NewsApiService implements ArticleApiInterface
{
    public function fetchArticles(): array
    {
        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'apiKey' => config('services.newsapi.key'),
            'country' => 'us',
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch articles from NewsAPI.');
        }

        return $response->json('articles');
    }
}
