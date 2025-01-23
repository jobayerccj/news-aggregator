<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserPreferenceService;
use App\Http\Controllers\Controller;
use App\Services\ArticleManagerService;

class UserPreferenceController extends Controller
{
    public function __construct(private UserPreferenceService $preferenceService)
    {
    }

    public function update(Request $request)
    {
        $request->validate([
            'authors' => 'array|exists:authors,id',
            'sources' => 'array|exists:sources,id',
            'categories' => 'array|exists:categories,id',
        ]);

        $user = auth()->user();
        $this->preferenceService->updatePreferences($user, $request->all());

        return response()->json(['message' => 'Preferences updated successfully.']);
    }

    public function index()
    {
        $user = auth()->user();
        $preferences = $this->preferenceService->getPreferences($user);

        return response()->json($preferences);
    }

    public function getUsersPreferredArticles(Request $request, ArticleManagerService $articleManagerService)
    {
        $user = $request->user();
        $articles = $articleManagerService->getArticlesByUserPreferences($user);

        return response()->json($articles);
    }
}
