<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UtilityType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'ten_loai_tien_ich',
        'mo_ta',
        'sap_xep',
    ];

    public function utilities(): HasMany
    {
        return $this->hasMany(Utility::class);
    }
}
