<?php

namespace App\Models;

use App\Models\HelpDesk;
use App\Models\Office;
use App\Models\VirtualOffice;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $guarded = [];

    public function office(){
        return $this->belongsTo(Office::class);
    }

    public function hotdesk(){
        return $this->belongsTo(HelpDesk::class);
    }

    public function virtuals(){
        return $this->belongsTo(VirtualOffice::class);
    }
}
