<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'author_id' => $this->faker->randomDigitNotNull(),
            'source_id' => $this->faker->randomDigitNotNull(),
        ];
    }
}
