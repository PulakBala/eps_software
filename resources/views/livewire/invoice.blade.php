<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Invoice /</span> List
        </h4>

        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" wire:model.live="search" class="form-control" placeholder="Search by invoice number, customer name, date...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Invoice List</h5>
                <button class="btn btn-primary" wire:click="$set('showForm', true)" wire:loading.attr="disabled">
                    <i class="bx bx-plus"></i> Create New Invoice
                </button>
            </div>

            @if($showForm)
            <div class="card-body">
                <form wire:submit.prevent="{{ $editingInvoice ? 'updateInvoice' : 'createInvoice' }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" wire:model="customer_name">
                            @error('customer_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Phone</label>
                            <input type="text" class="form-control" wire:model="customer_phone">
                            @error('customer_phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Address</label>
                            <textarea class="form-control" wire:model="customer_address"></textarea>
                            @error('customer_address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Email</label>
                            <input type="email" class="form-control" wire:model="customer_email">
                            @error('customer_email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bill Date</label>
                            <input type="date" class="form-control" wire:model="bill_date">
                            @error('bill_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" wire:model="delivery_date">
                            @error('delivery_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" wire:model="payment_method">
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                            @error('payment_method') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" class="form-control" wire:model="total_amount">
                            @error('total_amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Amount</label>
                            <input type="number" class="form-control" wire:model="tax_amount">
                            @error('tax_amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" wire:model="status">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Note</label>
                            <textarea class="form-control" wire:model="note"></textarea>
                            @error('note') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            {{ $editingInvoice ? 'Update Invoice' : 'Create Invoice' }}
                        </button>
                        <button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">Cancel</button>
                    </div>
                </form>
            </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Delivery Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->customer_name }}</td>
                            <td>{{ $invoice->bill_date->format('d M Y') }}</td>
                            <td>{{ $invoice->delivery_date->format('d M Y') }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('invoice.view', $invoice->id) }}" class="btn btn-sm btn-info">View</a>
                                <button class="btn btn-sm btn-primary" wire:click="editInvoice({{ $invoice->id }})">Edit</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div> 