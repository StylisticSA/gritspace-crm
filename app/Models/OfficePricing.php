<?php

namespace App\Models;

use App\Models\Note;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class OfficePricing extends Model
{
    protected $fillable = ['category_name','pricing_type', 'rate'];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    public function category()
    {
        return $this->belongsToMany(Category::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }
}
