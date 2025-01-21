<?php

namespace App\Adapters;

use App\Contracts\ArticleApiAdapterInterface;
use App\DTO\ArticleDTO;

class NewYorkTimesApiAdapter implements ArticleApiAdapterInterface
{
    public function transform(array $apiArticle): ArticleDTO
    {
        return new ArticleDTO(
            $apiArticle['headline']['main'] ?? 'Untitled',
            $apiArticle['lead_paragraph'] ?? '',
            $apiArticle['web_url'] ?? '',
            $apiArticle['source'] ?? 'Unknown',
            'New York Times',
            $apiArticle['news_desk'] ?? '',
        );
    }
}
