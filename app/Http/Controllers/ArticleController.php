<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index()
    {
        return Article::select('id', 'title', 'description', 'author', 'published_at', 'url_to_image')->get();
    }

    public function show($id)
    {
        return Article::findOrFail($id);
    }

    public function addToFavorites($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $article = Article::findOrFail($id);
        if ($user->favorites()->where('article_id', $article->id)->exists()) {
            return response()->json(['error' => 'Article already added to favorites'], 400);
        }
        $user->favorites()->attach($article->id);
        return response()->json(['message' => 'Article added to favorites'], 200);
    }

    public function removeFromFavorites($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $article = Article::findOrFail($id);
        if (!$user->favorites()->where('article_id', $article->id)->exists()) {
            return response()->json(['error' => 'Article not found in favorites'], 400);
        }
        $user->favorites()->detach($article->id);
        return response()->json(['message' => 'Article removed from favorites'], 200);
    }
}
