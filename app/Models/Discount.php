<?php

namespace App\Models;

use App\Models\HelpDesk;
use App\Models\Office;
use App\Models\User;
use App\Models\VirtualOffice;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function hotdesk()
    {
        return $this->belongsTo(HelpDesk::class, 'help_desk_id');
    }

    public function virtuals()
    {
        return $this->belongsTo(VirtualOffice::class, 'virtual_office_id');
    }
}
