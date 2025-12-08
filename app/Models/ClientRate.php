<?php

namespace App\Models;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ClientRate extends Model
{
    protected $guarded = [];

    public function rateable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope to get the current active rate
    public function scopeCurrent($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('effective_to')
              ->orWhere('effective_to', '>', now());
        })->latest('effective_from');
    }

    public function client()
    {
        return $this->belongsTo(ClientInformation::class, 'client_information_id');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }
}
