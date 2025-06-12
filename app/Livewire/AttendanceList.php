<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Services\ZKTecoService;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceList extends Component
{
    use WithPagination;

    public $search = '';
    public $date = '';
    public $showForm = false;
    public $editingAttendance = null;
    public $employee_id;
    public $check_in;
    public $check_out;
    public $status;
    public $notes;

    protected $rules = [
        'employee_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'check_in' => 'nullable|date_format:H:i',
        'check_out' => 'nullable|date_format:H:i|after:check_in',
        'status' => 'required|in:present,absent,late,half_day',
        'notes' => 'nullable|string'
    ];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDate()
    {
        $this->resetPage();
    }

    public function syncAttendance()
    {
        $service = new ZKTecoService();
        $success = $service->syncAttendance();

        if ($success) {
            session()->flash('message', 'Attendance synced successfully!');
        } else {
            session()->flash('error', 'Failed to sync attendance data.');
        }
    }

    public function createAttendance()
    {
        $this->validate();

        Attendance::create([
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'status' => $this->status,
            'notes' => $this->notes
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Attendance recorded successfully!');
    }

    public function editAttendance($id)
    {
        $this->editingAttendance = Attendance::findOrFail($id);
        $this->employee_id = $this->editingAttendance->employee_id;
        $this->date = $this->editingAttendance->date;
        $this->check_in = $this->editingAttendance->check_in;
        $this->check_out = $this->editingAttendance->check_out;
        $this->status = $this->editingAttendance->status;
        $this->notes = $this->editingAttendance->notes;
        $this->showForm = true;
    }

    public function updateAttendance()
    {
        $this->validate();

        $this->editingAttendance->update([
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'status' => $this->status,
            'notes' => $this->notes
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Attendance updated successfully!');
    }

    public function render()
    {
        $query = Attendance::query()
            ->with('employee')
            ->when($this->search, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->date, function($query) {
                $query->whereDate('date', $this->date);
            });

        $attendances = $query->latest()->paginate(10);

        return view('livewire.attendance-list', [
            'attendances' => $attendances
        ]);
    }
}
