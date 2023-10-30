<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Surcharge extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'utility_id',
        'name',
        'price',
    ];

    public function invoiceable()
    {
        return $this->morphOne(Invoiceable::class, 'invoiceable');
    }


    public function utility()
    {
        return $this->belongsTo(Utility::clearBootedModels());
    }

}
