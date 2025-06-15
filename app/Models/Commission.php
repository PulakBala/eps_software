<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'type',
        'reference_id',
        'month',
        'year',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function salary()
    {
        return $this->belongsTo(Salary::class, ['employee_id', 'month', 'year'], ['employee_id', 'month', 'year']);
    }
} 