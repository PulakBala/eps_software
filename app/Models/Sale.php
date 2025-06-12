<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'customer_id',
        'sale_date',
        'product_name',
        'quantity',
        'price',
        'total_amount',
        'payment_status',
        'paid_amount',
        'delivery_status',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'customer_id');
    }

    public function getDueAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }
} 