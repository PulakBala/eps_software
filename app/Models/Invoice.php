<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_email',
        'bill_date',
        'delivery_date',
        'note',
        'tax_amount',
        'payment_method',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'bill_date' => 'date',
        'delivery_date' => 'date',
    ];
} 