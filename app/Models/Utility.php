<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Utility extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'building_id',
        'utility_type_id',
        'name',
        'registrable',
        'start_time',
        'end_time',
        'block',
        'price',
        'max_times',
        'charge_start_time',
        'charge_end_time',
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

    public function utilityType(): BelongsTo
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function surcharges(): HasMany
    {
        return $this->hasMany(Surcharge::class);
    }

    public function invoiceable()
    {
        return $this->morphOne(Invoiceable::class, 'invoiceable');
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class)
            ->withPivot([
                'thoi_gian',
                'thoi_gian_bat_dau',
                'thoi_gian_ket_thuc',
                'so_luong',
                'muc_thu',
                'thanh_tien'
            ])->using(RegistrationUtility::class);
    }
}
