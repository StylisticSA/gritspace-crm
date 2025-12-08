<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * The user this note is about.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relationship to any module (office, boardroom, hotdesk, virtual).
     */
    public function module()
    {
        return $this->morphTo();
    }
}
