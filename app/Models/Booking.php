<?php

namespace App\Models;

use App\Models\Note;
use App\Models\User;
use App\Models\Office;
use App\Models\Category;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\VirtualBookingFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'office_bookings';

    protected $casts = [
        'selected_dates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);

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
