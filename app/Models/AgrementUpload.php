<?php

namespace App\Models;

use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgrementUpload extends Model
{
    /** @use HasFactory<\Database\Factories\AgrementUploadFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
