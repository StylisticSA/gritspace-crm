<?php

namespace App\Models;

use App\Models\Note;
use App\Models\User;
use App\Models\Office;
use Illuminate\Database\Eloquent\Model;

class VirtualOfficeRate extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }
}
