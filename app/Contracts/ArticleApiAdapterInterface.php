<?php

namespace App\Contracts;

use App\DTO\ArticleDTO;

interface ArticleApiAdapterInterface
{
    public function transform(array $apiArticle): ArticleDTO;
}
