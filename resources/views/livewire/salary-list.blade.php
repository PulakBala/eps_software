<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Salary Management</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" wire:click="generateSalary">
                        <i class="bx bx-calculator me-1"></i> Generate Salary
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Search and Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by employee name..." wire:model.live="search">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" wire:model.live="month">
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" wire:model.live="year">
                            <option value="">Select Year</option>
                            @foreach(range(date('Y')-2, date('Y')+2) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Flash Messages -->
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Salary List Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Month</th>
                                <th>Basic Salary</th>
                                <th>Present Days</th>
                                <th>Absent Days</th>
                                <th>Late Days</th>
                                <th>Overtime Hours</th>
                                <th>Total Earnings</th>
                                <th>Total Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salaries as $salary)
                                <tr>
                                    <td>{{ $salary->employee->name }}</td>
                                    <td>{{ date('F Y', mktime(0, 0, 0, $salary->month, 1, $salary->year)) }}</td>
                                    <td>৳{{ number_format($salary->basic_salary, 2) }}</td>
                                    <td>{{ $salary->present_days }}</td>
                                    <td>{{ $salary->absent_days }}</td>
                                    <td>{{ $salary->late_days }}</td>
                                    <td>{{ $salary->overtime_hours }}</td>
                                    <td>৳{{ number_format($salary->total_earnings, 2) }}</td>
                                    <td>৳{{ number_format($salary->total_deductions, 2) }}</td>
                                    <td>৳{{ number_format($salary->net_salary, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $salary->status === 'paid' ? 'success' : 
                                            ($salary->status === 'pending' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst($salary->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($salary->status === 'pending')
                                                    <li>
                                                        <button class="dropdown-item" wire:click="updateSalaryStatus({{ $salary->id }}, 'paid')">
                                                            <i class="bx bx-check me-2"></i> Mark as Paid
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" wire:click="updateSalaryStatus({{ $salary->id }}, 'cancelled')">
                                                            <i class="bx bx-x me-2"></i> Cancel
                                                        </button>
                                                    </li>
                                                @endif
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addCommissionModal" wire:click="$set('employee_id', {{ $salary->employee_id }})">
                                                        <i class="bx bx-plus me-2"></i> Add Commission
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addDeductionModal" wire:click="$set('selectedSalaryId', {{ $salary->id }})">
                                                        <i class="bx bx-minus me-2"></i> Add Deduction
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addLoanDeductionModal" wire:click="$set('employee_id', {{ $salary->employee_id }})">
                                                        <i class="bx bx-minus me-2"></i> Add Loan Deduction
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#salaryDetailsModal" wire:click="getSalaryDetails({{ $salary->id }})">
                                                        <i class="bx bx-detail me-2"></i> View Details
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No salary records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $salaries->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Commission Modal -->
    <div class="modal fade" id="addCommissionModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Commission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="addCommission">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" wire:model="amount" step="0.01" min="0">
                            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" wire:model="type">
                                <option value="">Select Type</option>
                                <option value="sales">Sales Commission</option>
                                <option value="performance">Performance Bonus</option>
                                <option value="bonus">Other Bonus</option>
                            </select>
                            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Commission</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Loan Deduction Modal -->
    <div class="modal fade" id="addLoanDeductionModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Loan Deduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="addLoanDeduction">
                        <div class="mb-3">
                            <label class="form-label">Loan</label>
                            <select class="form-select" wire:model="loanId">
                                <option value="">Select Loan</option>
                                @foreach(\App\Models\Loan::where('employee_id', $employee_id)->where('status', 'active')->get() as $loan)
                                    <option value="{{ $loan->id }}">
                                        Loan #{{ $loan->id }} - ৳{{ number_format($loan->amount, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('loanId') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" wire:model="amount" step="0.01" min="0">
                            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Deduction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Details Modal -->
    <div class="modal fade" id="salaryDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Salary Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($salaryDetails)
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Deductions</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salaryDetails['deductions'] as $deduction)
                                            <tr>
                                                <td>{{ ucfirst($deduction->type) }}</td>
                                                <td>৳{{ number_format($deduction->amount, 2) }}</td>
                                                <td>{{ $deduction->date->format('Y-m-d') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th colspan="2">৳{{ number_format($salaryDetails['total_deductions'], 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Commissions</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salaryDetails['commissions'] as $commission)
                                            <tr>
                                                <td>{{ ucfirst($commission->type) }}</td>
                                                <td>৳{{ number_format($commission->amount, 2) }}</td>
                                                <td>{{ ucfirst($commission->status) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th colspan="2">৳{{ number_format($salaryDetails['total_commissions'], 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="text-center">No details available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Deduction Modal -->
    <div class="modal fade" id="addDeductionModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Deduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="addDeduction">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" wire:model="deductionType">
                                <option value="">Select Type</option>
                                <option value="loan">Loan</option>
                                <option value="absence">Absence</option>
                                <option value="penalty">Penalty</option>
                                <option value="pf">Provident Fund (PF)</option>
                            </select>
                            @error('deductionType') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" wire:model="deductionAmount" step="0.01" min="0">
                            @error('deductionAmount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" wire:model="deductionDescription" rows="3"></textarea>
                            @error('deductionDescription') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" wire:model="deductionDate">
                            @error('deductionDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Deduction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', (modalId) => {
                const modal = document.getElementById(modalId);
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        });
    </script>
</div> 