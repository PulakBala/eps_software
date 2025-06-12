<?php

namespace App\Livewire;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceSearch extends Component
{
    use WithPagination;

    public $search = '';
    public $showForm = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_address', 'like', '%' . $this->search . '%')
                      ->orWhereDate('bill_date', 'like', '%' . $this->search . '%')
                      ->orWhereDate('delivery_date', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.invoice', [
            'invoices' => $invoices
        ]);
    }
} 