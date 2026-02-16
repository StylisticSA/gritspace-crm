<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Printing;
use App\Models\Permission;
use App\Models\AgrementUpload;
use App\Models\HotDeskBooking;
use App\Models\VirtualBooking;
use App\Models\BoardroomBooking;
use App\Models\ClosedOfficeRate;
use App\Models\ClientInformation;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

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

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasRole($role)
    {
        $this->loadMissing('roles');

        $userRoles = $this->roles->pluck('name')->map(fn ($role) => strtolower($role));

        $roles = collect((array) $role)->map(fn ($r) => strtolower($r));

        return $userRoles->intersect($roles)->isNotEmpty();
    }

    public function hasPermission($permission)
    {
        $normalized = fn ($name) => strtolower(trim($name));

        $rolePermissions = $this->roles
            ->flatMap->permissions
            ->pluck('name')
            ->map($normalized);

        $directPermissions = $this->permissions
            ->pluck('name')
            ->map($normalized);

        return $rolePermissions
            ->merge($directPermissions)
            ->unique()
            ->contains($normalized($permission));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', strtolower($role));
        });
    }

    public function printing()
    {
        return $this->hasMany(Printing::class);
    }

    public function agreement()
    {
        return $this->hasMany(AgrementUpload::class);
    }


}
