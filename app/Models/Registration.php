<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Registration extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'apartment_id',
        'thoi_gian_dang_ky',
        'mo_ta',
        'nguoi_dang_ky',
        'phi_danng_ky',
        'phu_thu',
        'tong_tien',
        'da_thanh_toan',
    ];

    protected $casts = [
        'da_thanh_toan' => 'boolean',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class);
    }

    public function utilities(): BelongsToMany
    {
        return $this->belongsToMany(Utility::class)
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
