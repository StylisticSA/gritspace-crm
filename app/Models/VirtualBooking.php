<?php

namespace App\Models;

use App\Models\Note;
use App\Models\User;
use App\Models\VirtualOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VirtualBooking extends Model
{
    /** @use HasFactory<\Database\Factories\VirtualBookingFactory> */
    use HasFactory;
    use SoftDeletes;


    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function virtualOffice()
    {
        return $this->belongsTo(VirtualOffice::class);
    }

    protected $casts = [
        'selected_dates' => 'array',
    ];

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }



}
