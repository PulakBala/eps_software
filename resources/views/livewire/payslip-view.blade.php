<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Salary Payslip</h5>
            <div>
                <a href="{{ route('salary') }}" class="btn btn-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Salary List
                </a>
                <button class="btn btn-primary" wire:click="generatePdf">
                    <i class="bx bx-download me-1"></i> Download PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Employee Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Name:</th>
                            <td>{{ $salary->employee->name }}</td>
                        </tr>
                        <tr>
                            <th>Employee ID:</th>
                            <td>{{ $salary->employee->employee_id }}</td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td>{{ $salary->employee->department }}</td>
                        </tr>
                        <tr>
                            <th>Month:</th>
                            <td>{{ $month }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Salary Details</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Basic Salary:</th>
                            <td>BDT {{ number_format($salary->basic_salary, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Overtime Hours:</th>
                            <td>{{ $salary->overtime_hours }}</td>
                        </tr>
                        <tr>
                            <th>Commissions:</th>
                            <td>BDT {{ number_format($totalCommissions, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Earnings:</th>
                            <td>BDT {{ number_format($salary->total_earnings, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Deductions:</th>
                            <td>BDT {{ number_format($totalDeductions, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Net Salary:</th>
                            <td>BDT {{ number_format($salary->net_salary, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

       

            <div class="mt-4 text-center">
                <p class="text-muted">This is a computer-generated document and does not require a signature.</p>
                <p class="text-muted">Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>
</div>