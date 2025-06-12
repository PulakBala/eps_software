<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Inventory /</span> Stock Management
        </h4>

        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Product List</h5>
                <button class="btn btn-primary" wire:click="$set('showForm', true)">
                    <i class="bx bx-plus me-1"></i> Add New Product
                </button>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by product code, name or category..." wire:model.live="search">
                        </div>
                    </div>
                </div>

                @if($showForm)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $editingProduct ? 'Edit Product' : 'Add New Product' }}</h5>
                        <button class="btn btn-close" wire:click="$set('showForm', false)"></button>
                    </div>
                    <div class="card-body">
                        <form wire:submit="{{ $editingProduct ? 'updateProduct' : 'createProduct' }}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product Code</label>
                                    <input type="text" class="form-control" wire:model="product_code" {{ $editingProduct ? 'readonly' : '' }}>
                                    @error('product_code') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" class="form-control" wire:model="name">
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" wire:model="description" rows="3"></textarea>
                                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Purchase Price</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="purchase_price">
                                    @error('purchase_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Selling Price</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="selling_price">
                                    @error('selling_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control" wire:model="quantity">
                                    @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Low Stock Alert</label>
                                    <input type="number" class="form-control" wire:model="low_stock_alert">
                                    @error('low_stock_alert') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Unit</label>
                                    <select class="form-select" wire:model="unit">
                                        <option value="piece">Piece</option>
                                        <option value="kg">Kilogram</option>
                                        <option value="g">Gram</option>
                                        <option value="l">Liter</option>
                                        <option value="ml">Milliliter</option>
                                        <option value="box">Box</option>
                                        <option value="pack">Pack</option>
                                    </select>
                                    @error('unit') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category</label>
                                    <input type="text" class="form-control" wire:model="category">
                                    @error('category') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    {{ $editingProduct ? 'Update Product' : 'Add Product' }}
                                </button>
                                <button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Purchase Price</th>
                                <th>Selling Price</th>
                                <th>Stock</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>{{ $product->product_code }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($product->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $product->name }}</h6>
                                            @if($product->description)
                                                <small class="text-muted">{{ Str::limit($product->description, 30) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->category ?? 'N/A' }}</td>
                                <td>৳{{ number_format($product->purchase_price, 2) }}</td>
                                <td>৳{{ number_format($product->selling_price, 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-bold me-2">{{ $product->quantity }}</span>
                                        <small class="text-muted">{{ $product->unit }}</small>
                                    </div>
                                </td>
                                <td>{{ $product->unit }}</td>
                                <td>
                                    @if($product->isLowStock())
                                        <span class="badge bg-danger">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-inline-block">
                                        <button class="btn btn-sm btn-icon btn-primary me-1" wire:click="editProduct({{ $product->id }})">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-success" 
                                                wire:click="$dispatch('openModal', { component: 'update-stock', arguments: { productId: {{ $product->id }} }})">
                                            <i class="bx bx-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No products found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div> 