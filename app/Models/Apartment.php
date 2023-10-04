<?php

namespace App\Models;

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
        'name',
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

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class);
    }
}
