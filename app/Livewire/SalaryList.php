<?php

namespace App\Livewire;

use App\Models\Salary;
use App\Services\SalaryService;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;
use App\Models\Commission;
use App\Models\Loan;
use App\Models\Deduction;
use Illuminate\Support\Facades\DB;
use App\Models\SalaryDeduction;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class SalaryList extends Component
{
    use WithPagination;

    public $search = '';
    public $month;
    public $year;
    public $showForm = false;
    public $editingSalary = null;
    public $employee_id;
    public $status;
    public $amount;
    public $type;
    public $loanId;
    public $salaryDetails;
    public $deductionAmount;
    public $deductionType;
    public $deductionDescription;
    public $deductionDate;
    public $selectedSalaryId;

    protected $rules = [
        'employee_id' => 'required|exists:employees,id',
        'month' => 'required|integer|between:1,12',
        'year' => 'required|integer|min:2000',
        'status' => 'required|in:pending,paid,cancelled',
        'amount' => 'required|numeric|min:0',
        'type' => 'required|in:sales,performance,bonus',
        'loanId' => 'required|exists:loans,id',
        'deductionAmount' => 'required|numeric|min:0',
        'deductionType' => 'required|in:loan,absence,penalty,pf',
        'deductionDescription' => 'required|string',
        'deductionDate' => 'required|date'
    ];

    public function mount()
    {
        $this->month = date('n');
        $this->year = date('Y');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedMonth()
    {
        $this->resetPage();
    }

    public function updatedYear()
    {
        $this->resetPage();
    }

    public function generateSalary()
    {
        try {
            $employees = Employee::where('is_active', true)->get();
            $currentMonth = now()->month;
            $currentYear = now()->year;

            foreach ($employees as $employee) {
                // Get existing salary record if any
                $existingSalary = Salary::where('employee_id', $employee->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->first();

                // Get attendance records for the month
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->get();

                // Calculate present, absent, late days and leaves
                $presentDays = $attendances->where('status', Attendance::STATUS_PRESENT)->count();
                $absentDays = $attendances->where('status', Attendance::STATUS_ABSENT)->count();
                $lateDays = $attendances->where('status', Attendance::STATUS_LATE)->count();
                $leaveDays = $attendances->where('status', Attendance::STATUS_LEAVE)->count();
                $holidayDays = $attendances->where('status', Attendance::STATUS_HOLIDAY)->count();
                
                // Calculate total working days in the month
                $totalDays = $presentDays + $absentDays + $lateDays + $leaveDays + $holidayDays;

                // Calculate basic salary and overtime
                $basicSalary = $employee->basic_salary;
                
                // Calculate per day salary
                $perDaySalary = $basicSalary / config('office.working_days_per_month');
                
                // Calculate salary based on attendance
                $attendanceSalary = $perDaySalary * ($presentDays + $lateDays);
                
                // Calculate leave salary (if applicable)
                $leaveSalary = 0;
                if ($leaveDays > 0) {
                    // Get approved leaves
                    $approvedLeaves = $attendances->where('status', Attendance::STATUS_LEAVE)
                        ->whereIn('leave_type', [
                            Attendance::LEAVE_TYPE_SICK,
                            Attendance::LEAVE_TYPE_CASUAL,
                            Attendance::LEAVE_TYPE_ANNUAL,
                            Attendance::LEAVE_TYPE_MATERNITY,
                            Attendance::LEAVE_TYPE_PATERNITY
                        ])
                        ->count();
                    
                    // Calculate leave salary (only for approved leaves)
                    $leaveSalary = $perDaySalary * $approvedLeaves;
                }
                
                // Calculate holiday salary
                $holidaySalary = $perDaySalary * $holidayDays;
                
                // Update basic salary based on attendance
                $basicSalary = $attendanceSalary + $leaveSalary + $holidaySalary;

                // Calculate overtime hours
                $overtimeHours = 0;
                foreach ($attendances as $attendance) {
                    if ($attendance->check_in && $attendance->check_out) {
                        $checkIn = Carbon::parse($attendance->check_in);
                        $checkOut = Carbon::parse($attendance->check_out);
                        
                        // Calculate total working hours
                        $workingHours = $checkOut->diffInHours($checkIn);
                        
                        // If working hours is more than duty hours, count as overtime
                        if ($workingHours > config('office.duty_hours')) {
                            $overtimeHours += ($workingHours - config('office.duty_hours'));
                        }
                    }
                }

                // Calculate total commissions for the month
                $totalCommissions = Commission::where('employee_id', $employee->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->whereIn('status', ['approved', 'pending'])
                    ->sum('amount');

                // Get total deductions for the month
                $totalDeductions = SalaryDeduction::where('employee_id', $employee->id)
                    ->whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->sum('amount');

                // Calculate total earnings (basic salary + overtime + commissions)
                $totalEarnings = $basicSalary + ($overtimeHours * ($basicSalary / (config('office.duty_hours') * config('office.working_days_per_month'))) * config('office.overtime_rate')) + $totalCommissions;

                // Calculate net salary (total earnings - total deductions)
                $netSalary = $totalEarnings - $totalDeductions;

                // Create or update salary record
                Salary::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'month' => $currentMonth,
                        'year' => $currentYear
                    ],
                    [
                        'basic_salary' => $basicSalary,
                        'present_days' => $presentDays,
                        'absent_days' => $absentDays,
                        'late_days' => $lateDays,
                        'total_days' => $totalDays,
                        'overtime_hours' => $overtimeHours,
                        'commission_amount' => $totalCommissions,
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        // Preserve existing status if it was paid
                        'status' => $existingSalary && $existingSalary->status === 'paid' ? 'paid' : 'pending',
                        // Preserve payment date if it was paid
                        'payment_date' => $existingSalary ? $existingSalary->payment_date : null
                    ]
                );
            }

            session()->flash('message', 'Salary generated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating salary: ' . $e->getMessage());
        }
    }

    public function updateSalaryStatus($salaryId, $status)
    {
        $salary = Salary::findOrFail($salaryId);
        $salary->update(['status' => $status]);
        
        session()->flash('success', 'Salary status updated successfully.');
    }

    public function addCommission()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:sales,performance,bonus'
        ]);

        DB::transaction(function() {
            // Create commission record
            Commission::create([
                'employee_id' => $this->employee_id,
                'amount' => $this->amount,
                'type' => $this->type,
                'month' => $this->month,
                'year' => $this->year,
                'status' => 'pending'
            ]);

            // Update salary
            $salary = Salary::where('employee_id', $this->employee_id)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->first();

            if ($salary) {
                $salary->update([
                    'total_earnings' => $salary->total_earnings + $this->amount,
                    'net_salary' => $salary->net_salary + $this->amount
                ]);
            }
        });

        $this->reset(['amount', 'type']);
        session()->flash('message', 'Commission added successfully.');
    }

    public function addLoanDeduction()
    {
        $this->validate([
            'loanId' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0'
        ]);

        DB::transaction(function() {
            // Create deduction record
            Deduction::create([
                'employee_id' => $this->employee_id,
                'loan_id' => $this->loanId,
                'amount' => $this->amount,
                'type' => 'loan',
                'date' => now()
            ]);

            // Update salary
            $salary = Salary::where('employee_id', $this->employee_id)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->first();

            if ($salary) {
                $salary->update([
                    'total_deductions' => $salary->total_deductions + $this->amount,
                    'net_salary' => $salary->net_salary - $this->amount
                ]);
            }

            // Update loan
            $loan = Loan::find($this->loanId);
            $loan->update([
                'paid_amount' => $loan->paid_amount + $this->amount,
                'status' => $loan->paid_amount + $this->amount >= $loan->amount ? 'paid' : 'active'
            ]);
        });

        $this->reset(['loanId', 'amount']);
        session()->flash('success', 'Loan deduction added successfully.');
    }

    public function addDeduction()
    {
        try {
            $this->validate([
                'deductionAmount' => 'required|numeric|min:0',
                'deductionType' => 'required|in:loan,absence,penalty,pf',
                'deductionDescription' => 'required|string',
                'deductionDate' => 'required|date'
            ]);

            $salary = Salary::findOrFail($this->selectedSalaryId);
            
            // Create the deduction
            $deduction = SalaryDeduction::create([
                'salary_id' => $this->selectedSalaryId,
                'employee_id' => $salary->employee_id,
                'type' => $this->deductionType,
                'amount' => $this->deductionAmount,
                'description' => $this->deductionDescription,
                'date' => $this->deductionDate
            ]);

            // Update salary total deductions
            $totalDeductions = SalaryDeduction::where('salary_id', $this->selectedSalaryId)->sum('amount');
            $salary->update([
                'total_deductions' => $totalDeductions
            ]);

            // Reset form fields
            $this->reset(['deductionAmount', 'deductionType', 'deductionDescription', 'deductionDate', 'selectedSalaryId']);
            
            // Close modal
            $this->dispatch('close-modal', 'addDeductionModal');
            
            session()->flash('message', 'Deduction added successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error adding deduction: ' . $e->getMessage());
        }
    }

    public function getSalaryDetails($salaryId)
    {
        $salary = Salary::with('deductions')->findOrFail($salaryId);
        
        // Get commissions for this employee and month/year
        $commissions = Commission::where('employee_id', $salary->employee_id)
            ->where('month', $salary->month)
            ->where('year', $salary->year)
            ->get();
        
        $this->salaryDetails = [
            'deductions' => $salary->deductions,
            'total_deductions' => $salary->deductions->sum('amount'),
            'commissions' => $commissions,
            'total_commissions' => $commissions->sum('amount')
        ];
    }

    public function render()
    {
        $query = Salary::query()
            ->with('employee')
            ->when($this->search, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->month, function($query) {
                $query->where('month', $this->month);
            })
            ->when($this->year, function($query) {
                $query->where('year', $this->year);
            })
            ->when($this->status, function($query) {
                $query->where('status', $this->status);
            });

        $salaries = $query->latest()->paginate(10);

        return view('livewire.salary-list', [
            'salaries' => $salaries
        ]);
    }
} 