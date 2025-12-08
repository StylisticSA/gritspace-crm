<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name'];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }



}
