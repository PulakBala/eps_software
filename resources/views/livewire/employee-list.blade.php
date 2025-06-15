<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Employee Management</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-info" wire:click="checkDeviceConnection">
                        <i class="bx bx-wifi me-1"></i> Check Device Connection
                    </button>
                    <button class="btn btn-primary" wire:click="syncEmployees">
                        <i class="bx bx-sync me-1"></i> Sync from Device
                    </button>
                    <button class="btn btn-success" wire:click="$set('showForm', true)">
                        <i class="bx bx-plus me-1"></i> Add Employee
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Search Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by name, email, role..." wire:model.live="search">
                        </div>
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

                <!-- Employee Form Modal -->
                @if($showForm)
                    <div class="modal fade show" style="display: block;" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $editingEmployee ? 'Edit Employee' : 'Add New Employee' }}</h5>
                                    <button type="button" class="btn-close" wire:click="$set('showForm', false)"></button>
                                </div>
                                <div class="modal-body">
                                    <form wire:submit.prevent="{{ $editingEmployee ? 'updateEmployee' : 'createEmployee' }}">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" wire:model="name" placeholder="Enter employee name">
                                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" wire:model="email" placeholder="Enter email address">
                                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                                <select class="form-select" wire:model="role">
                                                    <option value="">Select Role</option>
                                                    <option value="Manager">Manager</option>
                                                    <option value="Supervisor">Supervisor</option>
                                                    <option value="Employee">Employee</option>
                                                </select>
                                                @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Card Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" wire:model="card_number" placeholder="Enter card number">
                                                @error('card_number') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Basic Salary <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>
                                                    <input type="number" class="form-control" wire:model="basic_salary" step="0.01" min="0" placeholder="Enter basic salary">
                                                </div>
                                                @error('basic_salary') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Status</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" wire:model="is_active">
                                                    <label class="form-check-label">{{ $is_active ? 'Active' : 'Inactive' }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end mt-4">
                                            <button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">Cancel</button>
                                            <button type="submit" class="btn btn-primary">
                                                {{ $editingEmployee ? 'Update Employee' : 'Create Employee' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-backdrop fade show"></div>
                @endif

                <!-- Employee List Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Card Number</th>
                                <th>Basic Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $employee->role }}</span>
                                    </td>
                                    <td>{{ $employee->card_number }}</td>
                                    <td>৳{{ number_format($employee->basic_salary, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }}">
                                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-inline-block">
                                            <button class="btn btn-sm btn-icon btn-primary" wire:click="editEmployee({{ $employee->id }})" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-danger" wire:click="deleteEmployee({{ $employee->id }})" onclick="confirm('Are you sure you want to delete this employee?') || event.stopImmediatePropagation()" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No employees found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>
</div> 