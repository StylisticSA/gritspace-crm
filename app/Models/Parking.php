<?php

namespace App\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parking extends Model
{
    /** @use HasFactory<\Database\Factories\ParkingFactory> */
    use HasFactory;

    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
