<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Services\ZKTecoService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class EmployeeList extends Component
{
    use WithPagination;

    public $search = '';
    public $showForm = false;
    public $editingEmployee = null;
    public $name = '';
    public $email = '';
    public $role = '';
    public $card_number = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'role' => 'required',
        'card_number' => 'required|unique:employees,card_number',
        'is_active' => 'boolean'
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->role = '';
        $this->card_number = '';
        $this->is_active = true;
        $this->editingEmployee = null;
    }

    public function checkDeviceConnection()
    {
        try {
            $zkService = new ZKTecoService();
            $result = $zkService->testConnection();
            
            if ($result['success']) {
                session()->flash('message', 'Device connection successful! ' . $result['message']);
            } else {
                session()->flash('error', 'Device connection failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Device connection error: ' . $e->getMessage());
            session()->flash('error', 'Failed to connect to device: ' . $e->getMessage());
        }
    }

    public function syncEmployees()
    {
        try {
            $zkService = new ZKTecoService();
            $employees = $zkService->getEmployees();
            
            if (empty($employees)) {
                session()->flash('error', 'No employees found in the device');
                return;
            }

            $count = 0;
            foreach ($employees as $employee) {
                Employee::updateOrCreate(
                    ['device_user_id' => $employee['user_id']],
                    [
                        'name' => $employee['name'],
                        'email' => $employee['email'] ?? '',
                        'role' => $employee['role'] ?? 'Employee',
                        'card_number' => $employee['card_number'],
                        'is_active' => true
                    ]
                );
                $count++;
            }

            session()->flash('message', "Successfully synced {$count} employees from device");
        } catch (\Exception $e) {
            Log::error('Employee sync error: ' . $e->getMessage());
            session()->flash('error', 'Failed to sync employees: ' . $e->getMessage());
        }
    }

    public function createEmployee()
    {
        $this->validate();

        try {
            Employee::create([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'card_number' => $this->card_number,
                'is_active' => $this->is_active
            ]);

            session()->flash('message', 'Employee created successfully');
            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Employee creation error: ' . $e->getMessage());
            session()->flash('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    public function editEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $this->editingEmployee = $employee;
        $this->name = $employee->name;
        $this->email = $employee->email;
        $this->role = $employee->role;
        $this->card_number = $employee->card_number;
        $this->is_active = $employee->is_active;
        $this->showForm = true;
    }

    public function updateEmployee()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'role' => 'required',
            'card_number' => 'required|unique:employees,card_number,' . $this->editingEmployee->id,
            'is_active' => 'boolean'
        ]);

        try {
            $this->editingEmployee->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'card_number' => $this->card_number,
                'is_active' => $this->is_active
            ]);

            session()->flash('message', 'Employee updated successfully');
            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Employee update error: ' . $e->getMessage());
            session()->flash('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    public function deleteEmployee($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();
            session()->flash('message', 'Employee deleted successfully');
        } catch (\Exception $e) {
            Log::error('Employee deletion error: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $employees = Employee::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('role', 'like', '%' . $this->search . '%')
            ->orWhere('card_number', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.employee-list', [
            'employees' => $employees
        ]);
    }
} 