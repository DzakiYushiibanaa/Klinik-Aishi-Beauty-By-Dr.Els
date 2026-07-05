<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class OrderInvoiceController extends Controller
{
    public function sendEmail($kode_order)
    {
        \Log::info('Email: Request received', ['kode' => $kode_order, 'user' => auth()->id()]);
        
        try {
            $order = Order::where('user_id', auth()->id())
                ->where('kode_order', $kode_order)
                ->with(['items.product', 'items.treatment', 'user.pasienProfile'])
                ->firstOrFail();

            \Log::info('Email: Order found, generating PDF...');

            $pdf = Pdf::loadView('pdf.invoice', compact('order'))
                ->setPaper('a4', 'portrait');
            
            $pdfOutput = $pdf->output();
            \Log::info('Email: PDF generated', ['size' => strlen($pdfOutput)]);

            $email = auth()->user()->email;
            \Log::info('Email: Sending to ' . $email);
            
            Mail::to($email)->send(new InvoiceMail($order, $pdfOutput));

            \Log::info('Email: Sent successfully');

            return response()->json(['success' => true, 'message' => 'Nota pesanan berhasil dikirim ke email: ' . $email]);
        } catch (\Exception $e) {
            \Log::error('Invoice Email Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['success' => false, 'error' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }
}
