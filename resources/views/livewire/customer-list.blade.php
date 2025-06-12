<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Customer /</span> List
        </h4>

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, phone, email or address...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Total Invoices</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $customer->customer_name }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->customer_phone }}</td>
                            <td>{{ $customer->customer_email ?? 'N/A' }}</td>
                            <td>{{ $customer->customer_address }}</td>
                            <td>
                                {{ \App\Models\Invoice::where('customer_name', $customer->customer_name)->count() }}
                            </td>
                            <td>
                                ৳ {{ number_format(\App\Models\Invoice::where('customer_name', $customer->customer_name)->sum('total_amount'), 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div> 