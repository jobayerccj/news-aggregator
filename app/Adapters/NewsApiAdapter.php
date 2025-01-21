<?php

namespace App\Adapters;

use App\Contracts\ArticleApiAdapterInterface;
use App\DTO\ArticleDTO;

class NewsApiAdapter implements ArticleApiAdapterInterface
{
    public function transform(array $apiArticle): ArticleDTO
    {
        return new ArticleDTO(
            $apiArticle['title'] ?? 'Untitled',
            $apiArticle['content'] ?? '',
            $apiArticle['url'] ?? '',
            $apiArticle['source']['name'] ?? 'Unknown',
            $apiArticle['author'] ?? 'Unknown',
            '',
        );
    }
}
