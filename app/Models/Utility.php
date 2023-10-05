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

    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class);
    }

    public function operatingTimes(): BelongsToMany
    {
        return $this->belongsToMany(OperatingTime::class);
    }

    public function utilitiesRegistration(): HasMany
    {
        return $this->hasMany(UtilityRegistration::class);
    }
}
