<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    // Define the relationship: A category has many articles
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }
}
