<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'loan_id',
        'amount',
        'type',
        'date'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function salary()
    {
        return $this->belongsTo(Salary::class);
    }
} 