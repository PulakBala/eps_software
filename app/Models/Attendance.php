<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
        'leave_type'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime'
    ];

    // Add constants for attendance status
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_HALF_DAY = 'half_day';
    const STATUS_LEAVE = 'leave';
    const STATUS_HOLIDAY = 'holiday';

    // Add constants for leave types
    const LEAVE_TYPE_SICK = 'sick';
    const LEAVE_TYPE_CASUAL = 'casual';
    const LEAVE_TYPE_ANNUAL = 'annual';
    const LEAVE_TYPE_MATERNITY = 'maternity';
    const LEAVE_TYPE_PATERNITY = 'paternity';
    const LEAVE_TYPE_UNPAID = 'unpaid';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
} 