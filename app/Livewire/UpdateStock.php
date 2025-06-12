<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class UpdateStock extends Component
{
    public $productId;
    public $product;
    public $quantity;
    public $type = 'add'; // add or subtract

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->product = Product::findOrFail($productId);
    }

    public function updateStock()
    {
        $this->validate([
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:add,subtract'
        ]);

        $quantity = $this->type === 'add' ? $this->quantity : -$this->quantity;
        
        if ($this->type === 'subtract' && $this->product->quantity < $this->quantity) {
            $this->addError('quantity', 'Insufficient stock available.');
            return;
        }

        $this->product->updateStock($quantity);
        $this->dispatch('closeModal');
        $this->dispatch('stockUpdated');
    }

    public function render()
    {
        return view('livewire.update-stock');
    }
} 