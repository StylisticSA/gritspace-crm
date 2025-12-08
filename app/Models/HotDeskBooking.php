<?php

namespace App\Models;

use App\Models\Note;
use App\Models\User;
use App\Models\HelpDesk;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HotDeskBooking extends Model
{
    /** @use HasFactory<\Database\Factories\HotDeskBookingFactory> */
    use HasFactory;
    use SoftDeletes;


    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function helpdesk()
    {
        return $this->belongsTo(HelpDesk::class);
    }

    protected $casts = [
        'selected_dates' => 'array',
        'time_slots' => 'array',
    ];

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }

    public function payments()
    {
        return $this->morphMany(PaymentGateway::class, 'payable');
    }


}
