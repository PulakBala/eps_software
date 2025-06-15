<?php

namespace App\Services;

use App\Models\Salary;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Commission;
use App\Models\SalaryDeduction;
use App\Models\Loan;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class SalaryService
{
    /**
     * Generate monthly salary for all employees
     */
    public function generateMonthlySalary($month, $year)
    {
        try {
            $employees = Employee::where('is_active', true)->get();
            
            foreach ($employees as $employee) {
                // Get attendance data for the month
                $attendance = $this->getMonthlyAttendance($employee->id, $month, $year);
                
                // Calculate basic salary
                $basicSalary = $employee->basic_salary;
                
                // Calculate present days
                $presentDays = $attendance['present_days'];
                
                // Calculate absent days
                $absentDays = $attendance['absent_days'];
                
                // Calculate late days
                $lateDays = $attendance['late_days'];
                
                // Calculate overtime hours
                $overtimeHours = $this->calculateOvertime($employee->id, $month, $year);
                
                // Calculate total days in month
                $totalDays = Carbon::create($year, $month, 1)->daysInMonth;
                
                // Calculate total earnings
                $totalEarnings = $this->calculateTotalEarnings(
                    $basicSalary,
                    $presentDays,
                    $totalDays,
                    $overtimeHours
                );
                
                // Calculate total deductions
                $totalDeductions = $this->calculateTotalDeductions(
                    $basicSalary,
                    $absentDays,
                    $lateDays,
                    $totalDays
                );
                
                // Calculate net salary
                $netSalary = $totalEarnings - $totalDeductions;
                
                // Create or update salary record
                Salary::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'month' => $month,
                        'year' => $year
                    ],
                    [
                        'basic_salary' => $basicSalary,
                        'total_days' => $totalDays,
                        'present_days' => $presentDays,
                        'absent_days' => $absentDays,
                        'late_days' => $lateDays,
                        'overtime_hours' => $overtimeHours,
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'status' => 'pending'
                    ]
                );
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Error generating monthly salary: ' . $e->getMessage());
            throw new Exception('Failed to generate monthly salary: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly attendance data for an employee
     */
    private function getMonthlyAttendance($employeeId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $presentDays = 0;
        $absentDays = 0;
        $lateDays = 0;
        
        foreach ($attendance as $record) {
            if ($record->status === 'present') {
                $presentDays++;
            } elseif ($record->status === 'absent') {
                $absentDays++;
            } elseif ($record->status === 'late') {
                $lateDays++;
            }
        }
        
        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays
        ];
    }

    /**
     * Calculate overtime hours for an employee
     */
    private function calculateOvertime($employeeId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get();
        
        $totalOvertime = 0;
        $dutyHours = config('office.duty_hours');
        
        foreach ($attendance as $record) {
            $checkIn = Carbon::parse($record->check_in);
            $checkOut = Carbon::parse($record->check_out);
            
            // Calculate total working hours
            $workedHours = $checkOut->diffInHours($checkIn);
            
            // If working hours is more than duty hours, count as overtime
            if ($workedHours > $dutyHours) {
                $totalOvertime += ($workedHours - $dutyHours);
            }
        }
        
        return $totalOvertime;
    }

    /**
     * Calculate total earnings
     */
    private function calculateTotalEarnings($basicSalary, $presentDays, $totalDays, $overtimeHours)
    {
        // Calculate daily rate
        $dailyRate = $basicSalary / $totalDays;
        
        // Calculate present days salary
        $presentDaysSalary = $dailyRate * $presentDays;
        
        // Calculate overtime salary using configured rate
        $overtimeRate = ($dailyRate / config('office.duty_hours')) * config('office.overtime_rate');
        $overtimeSalary = $overtimeRate * $overtimeHours;
        
        return $presentDaysSalary + $overtimeSalary;
    }

    /**
     * Calculate total deductions
     */
    private function calculateTotalDeductions($basicSalary, $absentDays, $lateDays, $totalDays)
    {
        // Calculate daily rate
        $dailyRate = $basicSalary / $totalDays;
        
        // Calculate absent days deduction
        $absentDeduction = $dailyRate * $absentDays;
        
        // Calculate late days deduction (assuming 25% of daily rate)
        $lateDeduction = ($dailyRate * 0.25) * $lateDays;
        
        return $absentDeduction + $lateDeduction;
    }

    /**
     * Get salary details for an employee
     */
    public function getEmployeeSalary($employeeId, $month, $year)
    {
        return Salary::where('employee_id', $employeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Update salary status
     */
    public function updateSalaryStatus($salaryId, $status)
    {
        $salary = Salary::findOrFail($salaryId);
        $salary->status = $status;
        $salary->payment_date = $status === 'paid' ? now() : null;
        $salary->save();
        
        return $salary;
    }

    /**
     * Add commission to salary
     */
    public function addCommission($employeeId, $amount, $type, $referenceId, $month, $year)
    {
        try {
            // Create commission record
            $commission = Commission::create([
                'employee_id' => $employeeId,
                'amount' => $amount,
                'type' => $type,
                'reference_id' => $referenceId,
                'month' => $month,
                'year' => $year,
                'status' => 'pending'
            ]);

            // Update salary record
            $salary = Salary::where('employee_id', $employeeId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($salary) {
                $salary->commission_amount += $amount;
                $salary->total_earnings += $amount;
                $salary->net_salary += $amount;
                $salary->save();
            }

            return $commission;
        } catch (Exception $e) {
            Log::error('Error adding commission: ' . $e->getMessage());
            throw new Exception('Failed to add commission: ' . $e->getMessage());
        }
    }

    /**
     * Add loan deduction to salary
     */
    public function addLoanDeduction($employeeId, $loanId, $amount, $month, $year)
    {
        try {
            // Create deduction record
            $deduction = SalaryDeduction::create([
                'employee_id' => $employeeId,
                'type' => 'loan',
                'amount' => $amount,
                'description' => 'Loan repayment',
                'date' => now(),
                'reference_id' => $loanId
            ]);

            // Update salary record
            $salary = Salary::where('employee_id', $employeeId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($salary) {
                $salary->total_deductions += $amount;
                $salary->net_salary -= $amount;
                $salary->save();
            }

            // Update loan record
            $loan = Loan::findOrFail($loanId);
            $loan->remaining_installments--;
            if ($loan->remaining_installments <= 0) {
                $loan->status = 'completed';
            }
            $loan->save();

            return $deduction;
        } catch (Exception $e) {
            Log::error('Error adding loan deduction: ' . $e->getMessage());
            throw new Exception('Failed to add loan deduction: ' . $e->getMessage());
        }
    }

    /**
     * Get employee's salary details with deductions and commissions
     */
    public function getSalaryDetails($employeeId, $month, $year)
    {
        $salary = Salary::with(['deductions', 'commissions'])
            ->where('employee_id', $employeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$salary) {
            return null;
        }

        return [
            'salary' => $salary,
            'deductions' => $salary->deductions,
            'commissions' => $salary->commissions,
            'total_deductions' => $salary->deductions->sum('amount'),
            'total_commissions' => $salary->commissions->sum('amount')
        ];
    }
} 