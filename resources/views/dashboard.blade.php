@section('title', __('Dashboard'))
<x-layouts.app :title="__('Dashboard')">
    <div class="row g-4">
        <!-- Sales Overview Cards -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Sales</h6>
                            <h4 class="mb-0">৳{{ number_format($totalSales ?? 0, 2) }}</h4>
                        </div>
                        <div class="avatar">
                            <div class="avatar-content bg-primary">
                                <i class="bx bx-cart text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Pending Deliveries</h6>
                            <h4 class="mb-0">{{ $pendingDeliveries ?? 0 }}</h4>
                        </div>
                        <div class="avatar">
                            <div class="avatar-content bg-warning">
                                <i class="bx bx-package text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Due Payments</h6>
                            <h4 class="mb-0">৳{{ number_format($duePayments ?? 0, 2) }}</h4>
                        </div>
                        <div class="avatar">
                            <div class="avatar-content bg-danger">
                                <i class="bx bx-money text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Customers</h6>
                            <h4 class="mb-0">{{ $totalCustomers ?? 0 }}</h4>
                        </div>
                        <div class="avatar">
                            <div class="avatar-content bg-success">
                                <i class="bx bx-user text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Sales Overview</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            This Month
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">This Week</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                            <li><a class="dropdown-item" href="#">This Year</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Sales</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales ?? [] as $sale)
                                <tr>
                                    <td>{{ $sale->sale_number }}</td>
                                    <td>৳{{ number_format($sale->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($sale->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No recent sales</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Deliveries -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Deliveries</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Customer</th>
                                    <th>Delivery Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingDeliveries ?? [] as $delivery)
                                <tr>
                                    <td>{{ $delivery->sale_number }}</td>
                                    <td>{{ $delivery->customer->customer_name }}</td>
                                    <td>{{ $delivery->delivery_date->format('Y-m-d') }}</td>
                                    <td>৳{{ number_format($delivery->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $delivery->delivery_status === 'delivered' ? 'success' : 'warning' }}">
                                            {{ ucfirst(str_replace('_', ' ', $delivery->delivery_status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No upcoming deliveries</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels ?? []) !!},
                datasets: [{
                    label: 'Sales',
                    data: {!! json_encode($chartData ?? []) !!},
                    borderColor: '#696cff',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(105, 108, 255, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-layouts.app>
