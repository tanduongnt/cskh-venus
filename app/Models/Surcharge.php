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
        'bat_buoc',
        'co_dinh',
        'thu_theo_block',
        'active'
    ];

    protected $casts = [
        'co_dinh' => 'boolean',
        'mac_dinh' => 'boolean',
        'thu_theo_block' => 'boolean',
        'active' => 'boolean',
    ];

    public function utility(): BelongsTo
    {
        return $this->belongsTo(Utility::class);
    }
}
