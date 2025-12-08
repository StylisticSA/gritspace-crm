<?php

namespace App\Models;

use App\Models\Extra;
use App\Models\Office;
use App\Models\HelpDesk;
use App\Models\Printing;
use App\Models\Boardroom;
use App\Models\VirtualOffice;
use App\Models\AgrementUpload;
use App\Models\ClientInformation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'city'];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    public function boardrooms()
    {
        return $this->hasMany(Boardroom::class);
    }

    public function virtualOffices()
    {
        return $this->hasMany(VirtualOffice::class);
    }

    public function helpDesk()
    {
        return $this->hasMany(HelpDesk::class);
    }

    public function printing()
    {
        return $this->hasMany(Printing::class);
    }

    public function clientInformation()
    {
        return $this->hasMany(ClientInformation::class);
    }

    public function extra()
    {
        return $this->hasMany(Extra::class);
    }

    public function agreement()
    {
        return $this->hasMany(AgrementUpload::class);
    }
}
