<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Sales List</h5>
                <button class="btn btn-primary" wire:click="$set('showForm', true)">
                    <i class="bx bx-plus me-1"></i> New Sale
                </button>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by sale number, product or customer..." wire:model.live="search">
                        </div>
                    </div>
                </div>

                @if($showForm)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $editingSale ? 'Edit Sale' : 'New Sale' }}</h5>
                        <button class="btn btn-close" wire:click="$set('showForm', false)"></button>
                    </div>
                    <div class="card-body">
                        <form wire:submit="{{ $editingSale ? 'updateSale' : 'createSale' }}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer</label>
                                    <select class="form-select" wire:model="customer_id">
                                        <option value="">Select Customer</option>
                                        @foreach(\App\Models\Invoice::select('id', 'customer_name')->distinct()->get() as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sale Date</label>
                                    <input type="date" class="form-control" wire:model="sale_date">
                                    @error('sale_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" class="form-control" wire:model="product_name">
                                    @error('product_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control" wire:model="quantity">
                                    @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="price">
                                    @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Total Amount</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="total_amount" readonly>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Payment Status</label>
                                    <select class="form-select" wire:model="payment_status">
                                        <option value="paid">Paid</option>
                                        <option value="due">Due</option>
                                        <option value="partial">Partial</option>
                                    </select>
                                    @error('payment_status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Paid Amount</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="paid_amount">
                                    @error('paid_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Delivery Status</label>
                                    <select class="form-select" wire:model="delivery_status">
                                        <option value="pending">Pending</option>
                                        <option value="delivered">Delivered</option>
                                    </select>
                                    @error('delivery_status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" wire:model="notes" rows="3"></textarea>
                                    @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    {{ $editingSale ? 'Update Sale' : 'Create Sale' }}
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
                                <th>Sale #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Delivery</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_number }}</td>
                                <td>{{ $sale->customer->customer_name }}</td>
                                <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                                <td>{{ $sale->product_name }}</td>
                                <td>{{ $sale->quantity }}</td>
                                <td>{{ number_format($sale->price, 2) }}</td>
                                <td>{{ number_format($sale->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $sale->delivery_status === 'delivered' ? 'success' : 'warning' }}">
                                        {{ ucfirst($sale->delivery_status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-icon btn-primary" wire:click="editSale({{ $sale->id }})">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No sales found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div> 