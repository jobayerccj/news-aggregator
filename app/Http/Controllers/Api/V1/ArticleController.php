<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with('author')->get();

        return response()->json($articles);
    }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'content' => 'required',
    //         'author_id' => 'required|exists:authors,id',
    //         'source_id' => 'required|exists:sources,id',
    //         'categories' => 'array|exists:categories,id', // Array of category IDs
    //     ]);

    //     $article = Article::create($validated);

    //     if (isset($validated['categories'])) {
    //         $article->categories()->sync($validated['categories']);
    //     }

    //     return response()->json($article->load('author', 'source', 'categories'), 201);
    // }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        $article->load('author');

        return response()->json($article);
    }
}
