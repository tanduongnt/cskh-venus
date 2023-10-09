<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use App\Enums\ApartmentCustomerRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function apartments(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class);
    }

    public function owns(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class)->withPivot(['role'])->wherePivot('role', ApartmentCustomerRole::OWNER);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class)->withPivot(['role'])->wherePivot('role', ApartmentCustomerRole::MEMBER);
    }

    // public function apartmentCustomers(): HasMany
    // {
    //     return $this->hasMany(ApartmentCustomer::class);
    // }
}
