<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Salary;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipView extends Component
{
    public $salary;
    public $totalDeductions;
    public $totalCommissions;
    public $month;

    public function mount($id)
    {
        try {
            $this->salary = Salary::with([
                'employee',
                'deductions'
            ])->findOrFail($id);
            
            $this->totalDeductions = $this->salary->deductions->sum('amount');
            $this->totalCommissions = $this->salary->commission_amount;
            $this->month = date('F Y', mktime(0, 0, 0, $this->salary->month, 1, $this->salary->year));
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading payslip: ' . $e->getMessage());
            $this->redirect(route('salary'));
        }
    }

    public function generatePdf()
    {
        try {
            $pdf = PDF::loadView('pdf.payslip', [
                'salary' => $this->salary,
                'totalDeductions' => $this->totalDeductions,
                'totalCommissions' => $this->totalCommissions,
                'month' => $this->month
            ]);
            
            $filename = "payslip_{$this->salary->employee->name}_{$this->salary->month}_{$this->salary->year}.pdf";
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.payslip-view');
    }
}