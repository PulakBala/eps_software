<?php

namespace App\Livewire;

use App\Models\Invoice;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceView extends Component
{
    public $invoice;

    public function mount($id)
    {
        $this->invoice = Invoice::findOrFail($id);
    }

    public function downloadPDF()
    {
        $pdf = PDF::loadView('livewire.invoice-view', ['invoice' => $this->invoice]);
        
        // Set PDF options for better styling
       
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "invoice-{$this->invoice->invoice_number}.pdf");
    }

    public function render()
    {
        return view('livewire.invoice-view');
    }
} 