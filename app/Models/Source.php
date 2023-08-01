<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'name',
        'description',
        'url',
    ];

    // Define the relationship: A source has many articles
    public function articles()
    {
        return $this->hasMany(Article::class, 'source_id');
    }
}
