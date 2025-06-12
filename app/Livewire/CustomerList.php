<?php

namespace App\Livewire;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Invoice::query()
            ->select('customer_name', 'customer_phone', 'customer_email', 'customer_address')
            ->distinct()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('customer_name')
            ->paginate(10);

        return view('livewire.customer-list', [
            'customers' => $customers
        ]);
    }
} 