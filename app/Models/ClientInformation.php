<?php

namespace App\Models;

use App\Models\Note;
use App\Models\User;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientInformation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rates()
    {
        return $this->hasMany(ClientRate::class, 'client_information_id');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'module');
    }

}
