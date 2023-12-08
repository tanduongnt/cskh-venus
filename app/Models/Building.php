<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Building extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'ten_toa_nha',
        'phi_quan_ly',
        'thue_vat',
        'so_luong_uy_quyen',
        'sap_xep',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }

    public function utilities(): HasMany
    {
        return $this->hasMany(Utility::class);
    }

    public function customers(): HasManyThrough
    {
        return $this->hasManyThrough(Customer::class, Apartment::class)->orderBy('sort');
    }
}
