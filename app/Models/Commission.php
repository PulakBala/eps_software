<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_id',
        'month',
        'year',
        'amount',
        'type',
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
        return $this->belongsTo(Salary::class);
    }
} 