<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Update Stock</h5>
        <button type="button" class="btn-close" wire:click="$dispatch('closeModal')"></button>
    </div>

    <div class="modal-body">
        <div class="mb-4">
            <h6 class="mb-2">Product Details</h6>
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                        {{ strtoupper(substr($product->name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <h6 class="mb-0">{{ $product->name }}</h6>
                    <small class="text-muted">Current Stock: {{ $product->quantity }} {{ $product->unit }}</small>
                </div>
            </div>
        </div>

        <form wire:submit="updateStock">
            <div class="mb-3">
                <label class="form-label">Update Type</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" wire:model="type" value="add" id="typeAdd">
                        <label class="form-check-label" for="typeAdd">Add Stock</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" wire:model="type" value="subtract" id="typeSubtract">
                        <label class="form-check-label" for="typeSubtract">Remove Stock</label>
                    </div>
                </div>
                @error('type') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" wire:model="quantity" min="1">
                @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    Update Stock
                </button>
                <button type="button" class="btn btn-secondary" wire:click="$dispatch('closeModal')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div> 