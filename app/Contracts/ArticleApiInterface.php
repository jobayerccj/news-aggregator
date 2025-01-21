<?php

namespace App\Contracts;

interface ArticleApiInterface
{
    public function fetchArticles(): array;
}
