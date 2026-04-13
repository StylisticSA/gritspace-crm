<?php

namespace App\Models;

use App\Models\Note;
use App\Models\Location;
use App\Models\HotDeskBooking;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;

class HelpDesk extends Model
{
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function hotdeskbookings()
    {
        return $this->hasMany(HotDeskBooking::class, 'helpdesk_id');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
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
