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
        'ma_can_ho',
        'dien_tich',
        'sap_xep',
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
        return $this->belongsToMany(Customer::class)->withPivot(['vai_tro', 'duoc_uy_quyen', 'customer_id']);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withPivot(['vai_tro'])->wherePivot('vai_tro', ApartmentCustomerRole::OWNER);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withPivot(['vai_tro'])->wherePivot('vai_tro', ApartmentCustomerRole::MEMBER);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
