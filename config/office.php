<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Office Time Settings
    |--------------------------------------------------------------------------
    |
    | This file contains the office time settings for attendance and salary
    | calculations.
    |
    | Time Format: All times should be in 24-hour format (HH:mm)
    | Example: '09:00' for 9:00 AM, '17:00' for 5:00 PM
    |
    */

    // Office start time (24-hour format)
    // Example: '09:00' means 9:00 AM
    'start_time' => '09:00',

    // Office end time (24-hour format)
    // Example: '17:00' means 5:00 PM
    'end_time' => '17:00',

    // Late grace period in minutes (after start time)
    // Example: 15 means employee can check in 15 minutes after start time
    'late_grace_period' => 15,

    // Standard duty hours per day
    'duty_hours' => 8,

    // Overtime rate multiplier
    'overtime_rate' => 1.5,

    // Working days per week
    'working_days_per_week' => 6,

    // Working days per month
    'working_days_per_month' => 26,

    /*
    |--------------------------------------------------------------------------
    | Time Format Examples
    |--------------------------------------------------------------------------
    |
    | 24-hour format examples:
    | '09:00' = 9:00 AM
    | '13:00' = 1:00 PM
    | '17:00' = 5:00 PM
    | '23:00' = 11:00 PM
    |
    */
]; 