<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'address',
        'floor',
        'apartment',
        'area',
        'investor',
        'description',
        'sort',
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
}
