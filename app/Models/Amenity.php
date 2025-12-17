<?php

namespace App\Models;

use App\Models\Office;
use App\Models\VirtualOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Amenity extends Model
{
    protected $guarded = [];

    public function offices()
    {
        return $this->belongsToMany(Office::class);
    }

    public function boardrooms()
    {
        return $this->belongsToMany(Boardroom::class, 'boardroom_amenity');
    }

    public function virtualOffices()
    {
        return $this->belongsToMany(VirtualOffice::class);
    }

    public function helpDesks()
    {
        return $this->belongsToMany(HelpDesk::class);
    }


}
