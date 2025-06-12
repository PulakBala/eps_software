<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'name',
        'description',
        'purchase_price',
        'selling_price',
        'quantity',
        'low_stock_alert',
        'unit',
        'category'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'integer',
        'low_stock_alert' => 'integer'
    ];

    public function isLowStock()
    {
        return $this->quantity <= $this->low_stock_alert;
    }

    public function updateStock($quantity)
    {
        $this->quantity += $quantity;
        $this->save();
    }
} 