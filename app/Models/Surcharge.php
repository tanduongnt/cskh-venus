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
        'name',
        'price',
        'fixed',
    ];


    protected $casts = [
        'fixed' => 'boolean',
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
