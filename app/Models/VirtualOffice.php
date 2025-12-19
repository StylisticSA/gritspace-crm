<?php

namespace App\Models;

use App\Models\Amenity;
use App\Models\Company;
use App\Models\Location;
use App\Models\PaymentGateway;
use App\Models\VirtualBooking;
use Illuminate\Database\Eloquent\Model;

class VirtualOffice extends Model
{
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function bookings()
    {
        return $this->hasMany(VirtualBooking::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
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
