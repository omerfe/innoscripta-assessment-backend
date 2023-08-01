<?php

namespace App\Http\Controllers;

use App\Models\Source;

class SourceController extends Controller
{
    public function index()
    {
        return Source::all();
    }

    public function show($id)
    {
        return Source::findOrFail($id);
    }
}
