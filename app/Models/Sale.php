<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'customer_id',
        'sale_date',
        'delivery_date',
        'product_id',
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
        'sale_date' => 'datetime',
        'delivery_date' => 'datetime',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getDueAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isDeliveryOverdue(): bool
    {
        if (!$this->delivery_date) {
            return false;
        }
        return $this->delivery_date->isPast() && $this->delivery_status !== 'delivered';
    }

    public function getDaysUntilDelivery(): ?int
    {
        if (!$this->delivery_date) {
            return null;
        }
        return now()->diffInDays($this->delivery_date, false);
    }

    public function getDeliveryStatusBadgeClass(): string
    {
        return match($this->delivery_status) {
            'not_started' => 'secondary',
            'in_progress' => 'warning',
            'completed' => 'info',
            'delivered' => 'success',
            default => 'secondary'
        };
    }

    public function scopeOverdue($query)
    {
        return $query->where('delivery_date', '<', now())
                    ->where('delivery_status', '!=', 'delivered');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('delivery_date', '>', now())
                    ->where('delivery_status', '!=', 'delivered');
    }

    public function scopeWeekly($query)
    {
        return $query->whereBetween('delivery_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeMonthly($query)
    {
        return $query->whereBetween('delivery_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }
} 