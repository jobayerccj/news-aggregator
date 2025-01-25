<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ArticleManagerService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(private ArticleManagerService $articleManagerService)
    {
    }
    public function index(Request $request)
    {
        $filters = $request->only(['keyword', 'date', 'category', 'source']);
        $perPage = $request->get('per_page', 10);

        return $this->articleManagerService->searchArticles($filters, $perPage);
    }

    public function show(int $articleId)
    {
        return $this->articleManagerService->getArticleDetails($articleId);
    }
}
