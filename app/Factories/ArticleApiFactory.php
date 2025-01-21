<?php

namespace App\Factories;

use App\Adapters\GuardianApiAdapter;
use App\Adapters\NewsApiAdapter;
use App\Adapters\NewYorkTimesApiAdapter;
use App\Services\GuardianApiService;
use App\Services\NewsApiService;
use App\Services\NewYorkTimesApiService;

class ArticleApiFactory
{
    public static function make(string $apiName)
    {
        return match ($apiName) {
            'newsapi' => [new NewsApiService(), new NewsApiAdapter()],
            'nytimes' => [new NewYorkTimesApiService(), new NewYorkTimesApiAdapter()],
            'guardian' => [new GuardianApiService(), new GuardianApiAdapter()],
            default => throw new \InvalidArgumentException("API '{$apiName}' not supported."),
        };
    }
}
