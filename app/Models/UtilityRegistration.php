<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UtilityRegistration extends Model
{
    use HasFactory;
    use HasUuids;


    protected $fillable = [
        'utility_id',
        'customer_id',
        'apartment_id',
        'date',
        'date_register',
        'start_time',
        'end_time',
        'price',
    ];

    public function utility(): BelongsTo
    {
        return $this->belongsTo(Utility::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
