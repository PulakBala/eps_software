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
    public $product_id;
    public $availableStock;

    protected $rules = [
        'customer_id' => 'required',
        'sale_date' => 'required|date',
        'delivery_date' => 'nullable|date|after_or_equal:sale_date',
        'product_id' => 'required|exists:products,id',
        'product_name' => 'required|string',
        'quantity' => 'required|numeric|min:1',
        'price' => 'required|numeric|min:0',
        'total_amount' => 'required|numeric|min:0',
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

    public function updateProductDetails()
    {
        if ($this->product_id) {
            $product = \App\Models\Product::find($this->product_id);
            if ($product) {
                $this->product_name = $product->name;
                $this->price = $product->selling_price;
                $this->availableStock = $product->quantity;
                $this->calculateTotal();
            }
        } else {
            $this->reset(['product_name', 'price', 'availableStock', 'quantity', 'total_amount']);
        }
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

        // Check if enough stock is available
        $product = \App\Models\Product::find($this->product_id);
        if ($product->quantity < $this->quantity) {
            session()->flash('error', 'Not enough stock available. Available stock: ' . $product->quantity);
            return;
        }

        // Generate sale number
        $lastSale = Sale::latest()->first();
        $saleNumber = $lastSale ? 'SALE-' . str_pad($lastSale->id + 1, 6, '0', STR_PAD_LEFT) : 'SALE-000001';

        // Create the sale
        $sale = Sale::create([
            'sale_number' => $saleNumber,
            'customer_id' => $this->customer_id,
            'sale_date' => $this->sale_date,
            'delivery_date' => $this->delivery_date,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_amount' => $this->total_amount,
            'payment_status' => $this->payment_status,
            'paid_amount' => $this->paid_amount,
            'delivery_status' => $this->delivery_status,
            'notes' => $this->notes
        ]);

        // Update product stock
        $product->decrement('quantity', $this->quantity);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Sale created successfully!');
    }

    public function editSale($id)
    {
        $sale = Sale::findOrFail($id);
        $this->editingSale = $sale;
        $this->customer_id = $sale->customer_id;
        $this->sale_date = $sale->sale_date;
        $this->delivery_date = $sale->delivery_date;
        $this->product_id = $sale->product_id;
        $this->product_name = $sale->product_name;
        $this->quantity = $sale->quantity;
        $this->price = $sale->price;
        $this->total_amount = $sale->total_amount;
        $this->payment_status = $sale->payment_status;
        $this->paid_amount = $sale->paid_amount;
        $this->delivery_status = $sale->delivery_status;
        $this->notes = $sale->notes;
        $this->showForm = true;
    }

    public function updateSale()
    {
        $this->validate();

        // Check if enough stock is available (considering the old quantity)
        $product = \App\Models\Product::find($this->product_id);
        $oldQuantity = $this->editingSale->quantity;
        $newQuantity = $this->quantity;
        
        if ($product->quantity + $oldQuantity < $newQuantity) {
            session()->flash('error', 'Not enough stock available. Available stock: ' . ($product->quantity + $oldQuantity));
            return;
        }

        // Update the sale
        $this->editingSale->update([
            'customer_id' => $this->customer_id,
            'sale_date' => $this->sale_date,
            'delivery_date' => $this->delivery_date,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_amount' => $this->total_amount,
            'payment_status' => $this->payment_status,
            'paid_amount' => $this->paid_amount,
            'delivery_status' => $this->delivery_status,
            'notes' => $this->notes
        ]);

        // Update product stock
        $product->increment('quantity', $oldQuantity); // Add back the old quantity
        $product->decrement('quantity', $newQuantity); // Subtract the new quantity

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