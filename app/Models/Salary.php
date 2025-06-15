<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'month',
        'year',
        'total_days',
        'present_days',
        'absent_days',
        'late_days',
        'overtime_hours',
        'commission_amount',
        'total_earnings',
        'total_deductions',
        'net_salary',
        'status',
        'payment_date'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'basic_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductions()
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }
} 