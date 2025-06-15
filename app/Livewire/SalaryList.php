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
            // Get all active employees
            $employees = \App\Models\Employee::where('is_active', true)->get();
            
            foreach ($employees as $employee) {
                // Skip if basic salary is not set
                if (!$employee->basic_salary) {
                    session()->flash('error', "Basic salary not set for employee: {$employee->name}");
                    continue;
                }

                // Calculate attendance
                $attendance = \App\Models\Attendance::where('employee_id', $employee->id)
                    ->whereMonth('date', $this->month)
                    ->whereYear('date', $this->year)
                    ->get();

                $presentDays = $attendance->where('status', 'present')->count();
                $absentDays = $attendance->where('status', 'absent')->count();
                $lateDays = $attendance->where('status', 'late')->count();
                $overtimeHours = $attendance->sum('overtime_hours');

                // Calculate total days in month
                $totalDays = \Carbon\Carbon::create($this->year, $this->month, 1)->daysInMonth;

                // Calculate salary
                $basicSalary = $employee->basic_salary;
                $perDaySalary = $basicSalary / 30; // Assuming 30 days month
                $overtimeRate = ($perDaySalary / 8) * 1.5; // 1.5x for overtime
                
                $totalEarnings = $basicSalary + ($overtimeHours * $overtimeRate);
                $totalDeductions = ($absentDays * $perDaySalary) + ($lateDays * ($perDaySalary * 0.25));
                $netSalary = $totalEarnings - $totalDeductions;

                // Create or update salary record
                \App\Models\Salary::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'month' => $this->month,
                        'year' => $this->year
                    ],
                    [
                        'basic_salary' => $basicSalary,
                        'present_days' => $presentDays,
                        'absent_days' => $absentDays,
                        'late_days' => $lateDays,
                        'overtime_hours' => $overtimeHours,
                        'total_days' => $totalDays,
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'status' => 'pending'
                    ]
                );
            }

            session()->flash('message', 'Salaries generated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating salaries: ' . $e->getMessage());
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