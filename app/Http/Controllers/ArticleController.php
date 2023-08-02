<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::select('id', 'title', 'description', 'author', 'published_at', 'url_to_image');

        // Sorting
        if ($request->has('sort_by')) {
            $sortField = $request->input('sort_by');
            $sortOrder = $request->input('sort_dir', 'asc'); // Default to ascending if not specified
            $query->orderBy($sortField, $sortOrder);
        }

        // Filtering
        if ($request->has('source')) {
            $query->where('source_name', $request->input('source'));
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->has('author')) {
            $query->where('author', 'like', '%' . $request->input('author') . '%');
        }

        // Pagination
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $articles = $query->paginate($perPage);

        return $articles;
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
