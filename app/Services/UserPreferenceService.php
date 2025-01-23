<?php

namespace App\Services;

use App\Models\User;

class UserPreferenceService
{
    public function updatePreferences(User $user, array $preferences): void
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
    }

    public function getPreferences(User $user): array
    {
        return [
            'authors' => $user->preferredAuthors()->pluck('authors.name')->toArray(),
            'sources' => $user->preferredSources()->pluck('sources.name')->toArray(),
            'categories' => $user->preferredCategories()->pluck('categories.name')->toArray(),
        ];
    }
}
