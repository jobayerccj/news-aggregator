<?php

namespace App\Services;

use App\Contracts\ArticleApiInterface;
use Illuminate\Support\Facades\Http;

class GuardianApiService implements ArticleApiInterface
{
    public function fetchArticles(): array
    {
        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => config('services.guardian.key'),
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch articles from Guardian API.');
        }

        return $response->json('response')['results'];
    }
}
