<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Sorting
        if ($request->has('sort_by')) {
            $sortField = $request->query('sort_by');
            $sortDirection = $request->query('sort_dir', 'asc');
            $query->orderBy($sortField, $sortDirection);
        }

        // Filtering (by name)
        if ($request->has('name')) {
            $name = $request->query('name');
            $query->where('name', 'LIKE', "%{$name}%");
        }

        // Pagination
        $perPage = $request->query('per_page', 10);
        $categories = $query->select('id', 'name', 'created_at')->paginate($perPage);

        return $categories;
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
