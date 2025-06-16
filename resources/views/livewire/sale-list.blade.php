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
                    <div class="col-md-6">
                        <select class="form-select" wire:model.live="filter">
                            <option value="all">All Deliveries</option>
                            <option value="overdue">Overdue Deliveries</option>
                            <option value="upcoming">Upcoming Deliveries</option>
                            <option value="weekly">This Week's Deliveries</option>
                            <option value="monthly">This Month's Deliveries</option>
                        </select>
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
                                    <label class="form-label">Delivery Date</label>
                                    <input type="date" class="form-control" wire:model="delivery_date">
                                    @error('delivery_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product</label>
                                    <select class="form-select" wire:model="product_id" wire:change="updateProductDetails">
                                        <option value="">Select Product</option>
                                        @foreach(\App\Models\Product::where('quantity', '>', 0)->get() as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-price="{{ $product->selling_price }}"
                                                    data-stock="{{ $product->quantity }}">
                                                {{ $product->name }} (Stock: {{ $product->quantity }} {{ $product->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control" wire:model="quantity" wire:change="calculateTotal">
                                    @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                                    @if($availableStock !== null)
                                        <small class="text-muted">Available Stock: {{ $availableStock }}</small>
                                    @endif
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="price" readonly>
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
                                        <option value="not_started">Not Started</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
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
                                <th>Sale Date</th>
                                <th>Delivery Date</th>
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
                            <tr @class(['table-danger' => $sale->isDeliveryOverdue()])>
                                <td>{{ $sale->sale_number }}</td>
                                <td>{{ $sale->customer->customer_name }}</td>
                                <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                                <td>
                                    {{ $sale->delivery_date ? $sale->delivery_date->format('Y-m-d') : 'N/A' }}
                                    @if($sale->getDaysUntilDelivery() !== null)
                                        <small class="d-block text-muted">
                                            {{ $sale->getDaysUntilDelivery() > 0 ? $sale->getDaysUntilDelivery() . ' days left' : abs($sale->getDaysUntilDelivery()) . ' days overdue' }}
                                        </small>
                                    @endif
                                </td>
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
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-{{ $sale->getDeliveryStatusBadgeClass() }} dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            {{ ucfirst(str_replace('_', ' ', $sale->delivery_status)) }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" wire:click.prevent="updateDeliveryStatus({{ $sale->id }}, 'not_started')">Not Started</a></li>
                                            <li><a class="dropdown-item" href="#" wire:click.prevent="updateDeliveryStatus({{ $sale->id }}, 'in_progress')">In Progress</a></li>
                                            <li><a class="dropdown-item" href="#" wire:click.prevent="updateDeliveryStatus({{ $sale->id }}, 'completed')">Completed</a></li>
                                            <li><a class="dropdown-item" href="#" wire:click.prevent="updateDeliveryStatus({{ $sale->id }}, 'delivered')">Delivered</a></li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-icon btn-primary" wire:click="editSale({{ $sale->id }})">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No sales found</td>
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