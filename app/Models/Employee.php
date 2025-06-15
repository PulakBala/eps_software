<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_user_id',
        'name',
        'email',
        'role',
        'card_number',
        'basic_salary',
        'is_active'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function deductions()
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
} 