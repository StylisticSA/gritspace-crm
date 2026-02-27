<?php

namespace App\Models;

use App\Models\Amenity;
use App\Models\BoardroomBooking;
use App\Models\FreeHours;
use App\Models\Location;
use App\Models\Note;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;

class Boardroom extends Model
{
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'boardroom_amenity');
    }

    public function boardroombookings()
    {
        return $this->hasMany(BoardroomBooking::class);
    }

    public function hours()
    {
        return $this->hasMany(FreeHours::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }

    public function payments()
    {
        return $this->morphMany(PaymentGateway::class, 'payable');
    }
}
