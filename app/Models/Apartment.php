<?php

namespace App\Models;

use App\Enums\ApartmentCustomerRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Apartment extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'building_id',
        'name',
        'description',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withPivot(['role', 'customer_id']);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withPivot(['role'])->wherePivot('role', ApartmentCustomerRole::OWNER);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withPivot(['role'])->wherePivot('role', ApartmentCustomerRole::MEMBER);
    }

    // public function apartmentCustomers(): HasMany
    // {
    //     return $this->hasMany(ApartmentCustomer::class);
    // }
}
