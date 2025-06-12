<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SaleList extends Component
{
    use WithPagination;

    public $search = '';
    public $showForm = false;
    public $editingSale = null;
    public $customer_id;
    public $sale_date;
    public $delivery_date;
    public $product_name;
    public $quantity;
    public $price;
    public $total_amount;
    public $payment_status = 'due';
    public $paid_amount = 0;
    public $delivery_status = 'not_started';
    public $notes;
    public $filter = 'all'; // all, overdue, upcoming, weekly, monthly

    protected $rules = [
        'customer_id' => 'required|exists:invoices,id',
        'sale_date' => 'required|date',
        'delivery_date' => 'required|date|after_or_equal:sale_date',
        'product_name' => 'required|string|min:3',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
        'payment_status' => 'required|in:paid,due,partial',
        'paid_amount' => 'required|numeric|min:0',
        'delivery_status' => 'required|in:not_started,in_progress,completed,delivered',
        'notes' => 'nullable|string'
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function updatedQuantity()
    {
        $this->calculateTotal();
    }

    public function updatedPrice()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if ($this->quantity && $this->price) {
            $this->total_amount = $this->quantity * $this->price;
        }
    }

    public function generateSaleNumber()
    {
        $lastSale = Sale::latest()->first();
        $number = $lastSale ? intval(substr($lastSale->sale_number, 3)) + 1 : 1;
        return 'SAL' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function createSale()
    {
        $this->validate();

        Sale::create([
            'sale_number' => $this->generateSaleNumber(),
            'customer_id' => $this->customer_id,
            'sale_date' => $this->sale_date,
            'delivery_date' => $this->delivery_date,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_amount' => $this->total_amount,
            'payment_status' => $this->payment_status,
            'paid_amount' => $this->paid_amount,
            'delivery_status' => $this->delivery_status,
            'notes' => $this->notes
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Sale created successfully!');
    }

    public function editSale($id)
    {
        $this->editingSale = Sale::findOrFail($id);
        $this->customer_id = $this->editingSale->customer_id;
        $this->sale_date = $this->editingSale->sale_date->format('Y-m-d');
        $this->delivery_date = $this->editingSale->delivery_date->format('Y-m-d');
        $this->product_name = $this->editingSale->product_name;
        $this->quantity = $this->editingSale->quantity;
        $this->price = $this->editingSale->price;
        $this->total_amount = $this->editingSale->total_amount;
        $this->payment_status = $this->editingSale->payment_status;
        $this->paid_amount = $this->editingSale->paid_amount;
        $this->delivery_status = $this->editingSale->delivery_status;
        $this->notes = $this->editingSale->notes;
        $this->showForm = true;
    }

    public function updateSale()
    {
        $this->validate();

        $this->editingSale->update([
            'customer_id' => $this->customer_id,
            'sale_date' => $this->sale_date,
            'delivery_date' => $this->delivery_date,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_amount' => $this->total_amount,
            'payment_status' => $this->payment_status,
            'paid_amount' => $this->paid_amount,
            'delivery_status' => $this->delivery_status,
            'notes' => $this->notes
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Sale updated successfully!');
    }

    public function updateDeliveryStatus($id, $status)
    {
        $sale = Sale::findOrFail($id);
        $sale->update(['delivery_status' => $status]);
        session()->flash('message', 'Delivery status updated successfully!');
    }

    public function render()
    {
        $query = Sale::query()
            ->with('customer')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('sale_number', 'like', '%' . $this->search . '%')
                      ->orWhere('product_name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function($q) {
                          $q->where('customer_name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filter, function($query) {
                return match($this->filter) {
                    'overdue' => $query->overdue(),
                    'upcoming' => $query->upcoming(),
                    'weekly' => $query->weekly(),
                    'monthly' => $query->monthly(),
                    default => $query
                };
            });

        $sales = $query->latest()->paginate(10);

        return view('livewire.sale-list', [
            'sales' => $sales
        ]);
    }
} 