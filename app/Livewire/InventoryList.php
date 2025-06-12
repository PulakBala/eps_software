<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryList extends Component
{
    use WithPagination;

    public $search = '';
    public $showForm = false;
    public $editingProduct = null;
    public $product_code;
    public $name;
    public $description;
    public $purchase_price;
    public $selling_price;
    public $quantity;
    public $low_stock_alert;
    public $unit = 'piece';
    public $category;

    protected $rules = [
        'product_code' => 'required|unique:products,product_code',
        'name' => 'required|min:3',
        'description' => 'nullable',
        'purchase_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'low_stock_alert' => 'required|integer|min:0',
        'unit' => 'required',
        'category' => 'nullable'
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function generateProductCode()
    {
        $lastProduct = Product::latest()->first();
        $number = $lastProduct ? intval(substr($lastProduct->product_code, 3)) + 1 : 1;
        return 'PRD' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function createProduct()
    {
        $this->validate();

        Product::create([
            'product_code' => $this->product_code,
            'name' => $this->name,
            'description' => $this->description,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'quantity' => $this->quantity,
            'low_stock_alert' => $this->low_stock_alert,
            'unit' => $this->unit,
            'category' => $this->category
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Product created successfully!');
    }

    public function editProduct($id)
    {
        $this->editingProduct = Product::findOrFail($id);
        $this->product_code = $this->editingProduct->product_code;
        $this->name = $this->editingProduct->name;
        $this->description = $this->editingProduct->description;
        $this->purchase_price = $this->editingProduct->purchase_price;
        $this->selling_price = $this->editingProduct->selling_price;
        $this->quantity = $this->editingProduct->quantity;
        $this->low_stock_alert = $this->editingProduct->low_stock_alert;
        $this->unit = $this->editingProduct->unit;
        $this->category = $this->editingProduct->category;
        $this->showForm = true;
    }

    public function updateProduct()
    {
        $this->validate([
            'product_code' => 'required|unique:products,product_code,' . $this->editingProduct->id,
            'name' => 'required|min:3',
            'description' => 'nullable',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_alert' => 'required|integer|min:0',
            'unit' => 'required',
            'category' => 'nullable'
        ]);

        $this->editingProduct->update([
            'product_code' => $this->product_code,
            'name' => $this->name,
            'description' => $this->description,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'quantity' => $this->quantity,
            'low_stock_alert' => $this->low_stock_alert,
            'unit' => $this->unit,
            'category' => $this->category
        ]);

        $this->reset();
        $this->showForm = false;
        session()->flash('message', 'Product updated successfully!');
    }

    public function updateStock($id, $quantity)
    {
        $product = Product::findOrFail($id);
        $product->updateStock($quantity);
        session()->flash('message', 'Stock updated successfully!');
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('product_code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('category', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.inventory-list', [
            'products' => $products
        ]);
    }
} 