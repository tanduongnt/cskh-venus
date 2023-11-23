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
        'ten_tien_ich',
        'cho_phep_dang_ky',
        'gio_bat_dau',
        'gio_ket_thuc',
        'block',
        'don_gia',
        'max_times',
        'gio_bat_dau_tinh_tien',
        'gio_ket_thuc_tinh_tien',
        'mo_ta_tien_ich',
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
                'mo_ta',
                'thoi_gian_bat_dau',
                'thoi_gian_ket_thuc',
                'so_luong',
                'muc_thu',
                'thanh_tien'
            ])->using(RegistrationUtility::class);
    }
}
