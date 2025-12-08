<?php

namespace App\Models;

use App\Models\Note;
use App\Models\User;
use App\Models\Boardroom;
use App\Models\BoardroomBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoardroomBooking extends Model
{
    /** @use HasFactory<\Database\Factories\BoardroomBookingFactory> */
    use HasFactory;
    use SoftDeletes;


    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boardroom()
    {
        return $this->belongsTo(Boardroom::class, 'boardroom_id');
    }

    protected $casts = [
        'selected_dates' => 'array',
        'selected_times' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }

}
