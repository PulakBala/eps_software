<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Employee Management</h5>
                <div class="d-flex gap-2">
                    <!-- <button class="btn btn-info" wire:click="checkDeviceConnection">
                        <i class="bx bx-wifi me-1"></i> Check Device Connection
                    </button>
                    <button class="btn btn-primary" wire:click="syncEmployees">
                        <i class="bx bx-sync me-1"></i> Sync from Device
                    </button>
                    <button class="btn btn-success" wire:click="$set('showForm', true)">
                        <i class="bx bx-plus me-1"></i> Add Employee
                    </button> -->
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
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $editingEmployee ? 'Edit Employee' : 'Add Employee' }}</h5>
                            <button class="btn btn-close" wire:click="$set('showForm', false)"></button>
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="{{ $editingEmployee ? 'updateEmployee' : 'createEmployee' }}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" wire:model="name">
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" wire:model="email">
                                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" wire:model="role">
                                        @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" class="form-control" wire:model="card_number">
                                        @error('card_number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" wire:model="is_active">
                                            <label class="form-check-label">{{ $is_active ? 'Active' : 'Inactive' }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        {{ $editingEmployee ? 'Update Employee' : 'Create Employee' }}
                                    </button>
                                    <button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
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
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->role }}</td>
                                    <td>{{ $employee->card_number }}</td>
                                    <td>
                                        <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }}">
                                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-icon btn-primary" wire:click="editEmployee({{ $employee->id }})">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-danger" wire:click="deleteEmployee({{ $employee->id }})" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No employees found</td>
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