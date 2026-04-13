<?php

namespace App\Models;

use App\Models\Boardroom;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeHours extends Model
{
    /** @use HasFactory<\Database\Factories\FreeHoursFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boardroom()
    {
        return $this->belongsTo(Boardroom::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }


}
