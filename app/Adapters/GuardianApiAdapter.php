<?php

namespace App\Adapters;

use App\Contracts\ArticleApiAdapterInterface;
use App\DTO\ArticleDTO;

class GuardianApiAdapter implements ArticleApiAdapterInterface
{
    public function transform(array $apiArticle): ArticleDTO
    {
        return new ArticleDTO(
            $apiArticle['webTitle'] ?? 'Untitled',
            'Unknown',
            $apiArticle['webUrl'] ?? '',
            'Guardian',
            $apiArticle['sectionName'] ?? '',
            'Guardian',
        );
    }
}
