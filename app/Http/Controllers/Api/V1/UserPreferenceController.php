<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ArticleManagerService;
use App\Services\UserPreferenceService;
use Illuminate\Http\Request;
use App\Http\Requests\UserPreferencesRequest;

class UserPreferenceController extends Controller
{
    public function __construct(private UserPreferenceService $preferenceService)
    {
    }

    public function update(UserPreferencesRequest $request)
    {
        
        return $this->preferenceService->updatePreferences(auth()->user(), $request->validated());

        //return response()->json(['message' => 'Preferences updated successfully.']);
    }

    public function index()
    {
        return $this->preferenceService->getPreferences(auth()->user());
    }

    public function getUsersPreferredArticles(Request $request, ArticleManagerService $articleManagerService)
    {
        return $articleManagerService->getArticlesByUserPreferences($request->user());
    }
}
