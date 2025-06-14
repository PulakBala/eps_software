<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendance Management</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-info" wire:click="checkDeviceConnection">
                        <i class="bx bx-wifi me-1"></i> Check Device Connection
                    </button>
                    <button class="btn btn-primary" wire:click="syncAttendance">
                        <i class="bx bx-sync me-1"></i> Sync from Device
                    </button>
                    <button class="btn btn-warning" wire:click="syncEmployees">
                        <i class="bx bx-user-plus me-1"></i> Sync Employees
                    </button>
                    <button class="btn btn-success" wire:click="$set('showForm', true)">
                        <i class="bx bx-plus me-1"></i> Add Attendance
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Search and Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by employee name..." wire:model.live="search">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <input type="date" class="form-control" wire:model.live="date">
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

                <!-- Attendance Form Modal -->
                @if($showForm)
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $editingAttendance ? 'Edit Attendance' : 'Add Attendance' }}</h5>
                            <button class="btn btn-close" wire:click="$set('showForm', false)"></button>
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="{{ $editingAttendance ? 'updateAttendance' : 'createAttendance' }}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Employee</label>
                                        <select class="form-select" wire:model="employee_id">
                                            <option value="">Select Employee</option>
                                            @foreach(\App\Models\User::all() as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" wire:model="date">
                                        @error('date') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Check In</label>
                                        <input type="time" class="form-control" wire:model="check_in">
                                        @error('check_in') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Check Out</label>
                                        <input type="time" class="form-control" wire:model="check_out">
                                        @error('check_out') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" wire:model="status">
                                            <option value="">Select Status</option>
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="half_day">Half Day</option>
                                        </select>
                                        @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" wire:model="notes" rows="3"></textarea>
                                        @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        {{ $editingAttendance ? 'Update Attendance' : 'Create Attendance' }}
                                    </button>
                                    <button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Attendance List Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->employee->name }}</td>
                                    <td>{{ $attendance->date->format('Y-m-d') }}</td>
                                    <td>{{ $attendance->check_in }}</td>
                                    <td>{{ $attendance->check_out }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $attendance->status === 'present' ? 'success' : 
                                            ($attendance->status === 'absent' ? 'danger' : 
                                            ($attendance->status === 'late' ? 'warning' : 'info')) 
                                        }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->notes }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-icon btn-primary" wire:click="editAttendance({{ $attendance->id }})">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No attendance records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
