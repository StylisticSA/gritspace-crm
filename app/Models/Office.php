<?php

namespace App\Models;

use App\Models\Note;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\Location;
use App\Models\OfficePricing;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'office_name',
        'office_type',
        'location_id',
        'seats',
        'monthly_rate',
        'daily_rate',
        'category_id',
        'price_premium',
        'price_standard',
        'is_available',
        'available_dates',
    ];

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function pricing()
    {
        return $this->belongsTo(OfficePricing::class, 'pricing_id');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'office_id');
    }

    public function payments()
    {
        return $this->morphMany(PaymentGateway::class, 'payable');
    }

}
