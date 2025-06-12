<?php

namespace App\Livewire;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public $search = '';
    public $showForm = false;
    public $editingInvoice = null;
    public $customer_name;
    public $customer_phone;
    public $customer_address;
    public $customer_email;
    public $bill_date;
    public $delivery_date;
    public $note;
    public $tax_amount = 0;
    public $payment_method;
    public $total_amount = 0;
    public $status = 'pending';

    protected $rules = [
        'customer_name' => 'required|min:3',
        'customer_phone' => 'required',
        'customer_address' => 'required',
        'customer_email' => 'nullable|email',
        'bill_date' => 'required|date',
        'delivery_date' => 'required|date|after_or_equal:bill_date',
        'payment_method' => 'required',
        'total_amount' => 'required|numeric|min:0',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::latest()->first();
        $number = $lastInvoice ? intval(substr($lastInvoice->invoice_number, 3)) + 1 : 1;
        return 'INV' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function createInvoice()
    {
        $this->validate();

        Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_email' => $this->customer_email,
            'bill_date' => $this->bill_date,
            'delivery_date' => $this->delivery_date,
            'note' => $this->note,
            'tax_amount' => $this->tax_amount,
            'payment_method' => $this->payment_method,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Invoice created successfully!');
    }

    public function editInvoice($id)
    {
        $this->editingInvoice = Invoice::findOrFail($id);
        $this->customer_name = $this->editingInvoice->customer_name;
        $this->customer_phone = $this->editingInvoice->customer_phone;
        $this->customer_address = $this->editingInvoice->customer_address;
        $this->customer_email = $this->editingInvoice->customer_email;
        $this->bill_date = $this->editingInvoice->bill_date->format('Y-m-d');
        $this->delivery_date = $this->editingInvoice->delivery_date->format('Y-m-d');
        $this->note = $this->editingInvoice->note;
        $this->tax_amount = $this->editingInvoice->tax_amount;
        $this->payment_method = $this->editingInvoice->payment_method;
        $this->total_amount = $this->editingInvoice->total_amount;
        $this->status = $this->editingInvoice->status;
        $this->showForm = true;
    }

    public function updateInvoice()
    {
        $this->validate();

        $this->editingInvoice->update([
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_email' => $this->customer_email,
            'bill_date' => $this->bill_date,
            'delivery_date' => $this->delivery_date,
            'note' => $this->note,
            'tax_amount' => $this->tax_amount,
            'payment_method' => $this->payment_method,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Invoice updated successfully!');
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