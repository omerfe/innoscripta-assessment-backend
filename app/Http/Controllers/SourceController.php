<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Source::query();

        // Sorting
        if ($request->has('sort_by')) {
            $sortField = $request->query('sort_by');
            $sortDirection = $request->query('sort_dir', 'asc');
            $query->orderBy($sortField, $sortDirection);
        }

        // Filtering (by name, description, and url)
        if ($request->has('name')) {
            $name = $request->query('name');
            $query->where('name', 'LIKE', "%{$name}%");
        }

        if ($request->has('description')) {
            $description = $request->query('description');
            $query->where('description', 'LIKE', "%{$description}%");
        }

        if ($request->has('url')) {
            $url = $request->query('url');
            $query->where('url', 'LIKE', "%{$url}%");
        }

        // Pagination
        $perPage = $request->query('per_page', 10);
        $sources = $query->select('id', 'name', 'description', 'url')->paginate($perPage);

        return $sources;
    }

    public function show($id)
    {
        return Source::findOrFail($id);
    }
}
