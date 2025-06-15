<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Services\ZKTecoService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Exception;

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
    public $basic_salary = 0;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'role' => 'required',
        'card_number' => 'required|unique:employees,card_number',
        'is_active' => 'boolean',
        'basic_salary' => 'required|numeric|min:0'
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
        $this->basic_salary = 0;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function checkDeviceConnection()
    {
        try {
            $zk = new ZKTecoService();
            $result = $zk->testConnection();
            
            if ($result['success']) {
                session()->flash('message', 'Device connection successful! ' . $result['message']);
            } else {
                session()->flash('error', 'Device connection failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error checking device connection: ' . $e->getMessage());
        }
    }

    public function syncEmployees()
    {
        try {
            $zk = new ZKTecoService();
            $result = $zk->testConnection();
            
            if (!$result['success']) {
                session()->flash('error', 'Cannot sync: ' . $result['message']);
                return;
            }

            $users = $zk->getUsers();
            
            if (empty($users)) {
                session()->flash('error', 'No users found in device');
                return;
            }

            $count = 0;
            foreach ($users as $user) {
                $employee = Employee::updateOrCreate(
                    ['card_number' => $user['userid']],
                    [
                        'name' => $user['name'],
                        'email' => $user['userid'] . '@example.com', // Default email
                        'role' => 'Employee', // Default role
                        'is_active' => true
                    ]
                );
                $count++;
            }

            session()->flash('message', "Successfully synced {$count} employees from device");
        } catch (\Exception $e) {
            session()->flash('error', 'Error syncing employees: ' . $e->getMessage());
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
                'is_active' => $this->is_active,
                'basic_salary' => $this->basic_salary
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
        $this->editingEmployee = Employee::findOrFail($id);
        $this->name = $this->editingEmployee->name;
        $this->email = $this->editingEmployee->email;
        $this->role = $this->editingEmployee->role;
        $this->card_number = $this->editingEmployee->card_number;
        $this->is_active = $this->editingEmployee->is_active;
        $this->basic_salary = $this->editingEmployee->basic_salary;
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
                'is_active' => $this->is_active,
                'basic_salary' => $this->basic_salary
            ]);

            session()->flash('message', 'Employee updated successfully');
            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Employee update error: ' . $e->getMessage());
            session()->flash('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    public function updateBasicSalary()
    {
        $this->validate();

        try {
            $this->editingEmployee->update([
                'basic_salary' => $this->basic_salary
            ]);

            session()->flash('message', 'Basic salary updated successfully');
            $this->editingEmployee = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating basic salary: ' . $e->getMessage());
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
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.employee-list', [
            'employees' => $employees
        ]);
    }
} 