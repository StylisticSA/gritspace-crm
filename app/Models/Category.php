<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Category;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function offices()
    {
        return $this->hasMany(Category::class);
    }

    protected $casts = [
        'offers_level' => 'boolean',
    ];

    public function clientInformation()
    {
        return $this->hasMany(ClientInformation::class);
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

 

}
