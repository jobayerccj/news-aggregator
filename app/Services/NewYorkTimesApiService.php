<?php

namespace App\Services;

use App\Contracts\ArticleApiInterface;
use Illuminate\Support\Facades\Http;

class NewYorkTimesApiService implements ArticleApiInterface
{
    public function fetchArticles(): array
    {
        $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
            'api-key' => config('services.nytimes.key'),
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch articles from New York Times API.');
        }

        return $response->json('response')['docs'];
    }
}
