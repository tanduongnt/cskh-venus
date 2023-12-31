<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use App\Enums\ApartmentCustomerRole;
use Illuminate\Support\Facades\Hash;
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
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    protected $fillable = [
        'ho_va_ten',
        'email',
        'so_dien_thoai',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function apartments(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class)->withPivot(['vai_tro', 'duoc_uy_quyen']);
    }

    public function authorizedPersons(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class)->withPivot(['duoc_uy_quyen'])->wherePivot('duoc_uy_quyen', true);
    }

    public function owns(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class)->withPivot(['vai_tro'])->wherePivot('vai_tro', ApartmentCustomerRole::OWNER);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class)->withPivot(['vai_tro'])->wherePivot('vai_tro', ApartmentCustomerRole::MEMBER);
    }

    public function buildings()
    {
        return $this->hasManyDeep(Building::class, ['apartment_customer', Apartment::class], ['customer_id', 'id', 'id'], [null, 'apartment_id', 'building_id']);
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class);
    }
}
