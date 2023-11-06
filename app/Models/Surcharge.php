<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surcharge extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'utility_id',
        'ten_phu_thu',
        'muc_thu',
        'mac_dinh',
        'co_dinh',
        'thu_theo_block'
    ];


    protected $casts = [
        'co_dinh' => 'boolean',
        'mac_dinh' => 'boolean',
        'thu_theo_block' => 'boolean',
    ];

    public function invoiceable()
    {
        return $this->morphOne(Invoiceable::class, 'invoiceable');
    }


    public function utility(): BelongsTo
    {
        return $this->belongsTo(Utility::class);
    }
}
