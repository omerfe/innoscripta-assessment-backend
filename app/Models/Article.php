<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'source_name',
        'category_id',
        'author',
        'title',
        'description',
        'url',
        'url_to_image',
        'published_at',
        'content',
    ];

    // Define the date fields
    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Disable created_at and updated_at if needed
    public $timestamps = false;

    // Define the relationship: An article belongs to a source
    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    // Define the relationship: An article belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
