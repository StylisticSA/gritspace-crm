<?php

namespace App\Models;

use App\Models\AgrementUpload;
use App\Models\BoardroomBooking;
use App\Models\ClientInformation;
use App\Models\ClosedOfficeRate;
use App\Models\FreeHours;
use App\Models\HotDeskBooking;
use App\Models\Permission;
use App\Models\Printing;
use App\Models\VirtualBooking;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function companyDetails()
    {
        return $this->hasOne(ClientInformation::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function virtualBookings()
    {
        return $this->hasMany(VirtualBooking::class);
    }

    public function HotDesk()
    {
        return $this->hasMany(HotDeskBooking::class);
    }

    public function boardroombookings()
    {
        return $this->hasMany(BoardroomBooking::class);
    }

    public function closedRates()
    {
        return $this->hasMany(ClosedOfficeRate::class);
    }

    public function hours()
    {
        return $this->hasMany(FreeHours::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function printing()
    {
        return $this->hasMany(Printing::class);
    }

    public function agreement()
    {
        return $this->hasMany(AgrementUpload::class);
    }

    public function freeHours()
    {
        return $this->hasMany(FreeHours::class);
    }
}
