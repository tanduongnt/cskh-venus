<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoiceable extends Model
{
    use HasFactory;
    use HasUuids;


    protected $fillable = [
        'invoiceable_type',
        'invoice_id',
        'registration_date',
        'start',
        'end',
        'price',
        'user_id',
    ];

    protected $casts = [
        //'invoiceable_type' => InvoiceableType::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceable()
    {
        return $this->morphTo();
    }
}
