<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function show($id)
    {
        return Category::findOrFail($id);
    }

    public function addToFavoriteCategories($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $category = Category::findOrFail($id);
        if ($user->favoriteCategories()->where('category_id', $category->id)->exists()) {
            return response()->json(['error' => 'Category already added to favorites'], 400);
        }
        $user->favoriteCategories()->attach($category->id);
        return response()->json(['message' => 'Category added to favorites'], 200);
    }

    public function removeFromFavoriteCategories($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $category = Category::findOrFail($id);
        if (!$user->favoriteCategories()->where('category_id', $category->id)->exists()) {
            return response()->json(['error' => 'Category not found in favorites'], 400);
        }
        $user->favoriteCategories()->detach($category->id);
        return response()->json(['message' => 'Category removed from favorites'], 200);
    }
}
