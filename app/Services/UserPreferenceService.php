<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserPreferenceService
{
    use ApiResponse;

    public function updatePreferences(User $user, array $preferences): JsonResponse
    {
        if (isset($preferences['authors'])) {
            $user->preferredAuthors()->sync($preferences['authors']);
        }

        if (isset($preferences['sources'])) {
            $user->preferredSources()->sync($preferences['sources']);
        }

        if (isset($preferences['categories'])) {
            $user->preferredCategories()->sync($preferences['categories']);
        }

        return $this->successResponse(null, 'Preferences updated successfully.');
    }

    public function getPreferences(User $user)
    {
        $preferences = [
            'authors' => $user->preferredAuthors()->pluck('authors.name')->toArray(),
            'sources' => $user->preferredSources()->pluck('sources.name')->toArray(),
            'categories' => $user->preferredCategories()->pluck('categories.name')->toArray(),
        ];

        return $this->successResponse($preferences);
    }
}
