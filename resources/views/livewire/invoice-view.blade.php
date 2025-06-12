<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">
                <span class="text-muted fw-light">Invoice /</span> View
            </h4>
            <div>
                <button class="btn btn-primary me-2" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i> Print
                </button>
                <button class="btn btn-success" wire:click="downloadPDF">
                    <i class="bx bx-download me-1"></i> Download PDF
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm" id="invoice-print">
    <div class="card-body px-5 py-4">
        {{-- Header --}}
        <div class="text-center mb-4 border-bottom pb-3">
            <img src="{{ asset('assets/img/logo.jpg') }}" alt="Company Logo" height="60">
            <h4 class="mt-2 mb-0 fw-bold text-primary">INVOICE</h4>
        </div>

        {{-- Invoice Info --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="fw-bold">Invoice Details:</h6>
                <p class="mb-1"><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</p>
                <p class="mb-1"><strong>Invoice Date:</strong> {{ $invoice->bill_date->format('d M Y') }}</p>
                <p class="mb-1"><strong>Delivery Date:</strong> {{ $invoice->delivery_date->format('d M Y') }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <h6 class="fw-bold">Payment Info:</h6>
                <p class="mb-1"><strong>Method:</strong> {{ ucfirst($invoice->payment_method) }}</p>
                <p class="mb-1">
                    <strong>Status:</strong> 
                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="mb-4">
            <h6 class="fw-bold">Customer Information:</h6>
            <div class="border rounded p-3 bg-light">
                <p class="mb-1"><strong>Name:</strong> {{ $invoice->customer_name }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $invoice->customer_phone }}</p>
                <p class="mb-1"><strong>Address:</strong> {{ $invoice->customer_address }}</p>
                @if($invoice->customer_email)
                    <p class="mb-0"><strong>Email:</strong> {{ $invoice->customer_email }}</p>
                @endif
            </div>
        </div>

        {{-- Payment Table --}}
        <div class="mb-4">
            <h6 class="fw-bold">Payment Summary:</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end">৳ {{ number_format($invoice->total_amount - $invoice->tax_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Tax:</strong></td>
                            <td class="text-end">৳ {{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Total:</strong></td>
                            <td class="text-end fw-bold fs-5">৳ {{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Note --}}
        @if($invoice->note)
            <div class="mb-3">
                <h6 class="fw-bold">Note:</h6>
                <div class="border-start border-3 border-primary ps-3">
                    <p class="mb-0">{{ $invoice->note }}</p>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="text-center mt-5 pt-3 border-top">
            <small class="text-muted">Thank you for your business!</small>
        </div>
    </div>
</div>

    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #invoice-print, #invoice-print * {
                visibility: visible;
            }
            #invoice-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .btn {
                display: none !important;
            }
        }
    </style>
</div> 