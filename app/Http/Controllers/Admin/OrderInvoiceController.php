<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\InvoicePrintService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class OrderInvoiceController extends Controller
{
    public function __construct(private InvoicePrintService $invoiceService) {}

    /**
     * Render the receipt in browser (auto-triggers window.print()).
     */
    public function print(Request $request, int $orderId): View
    {
        $order = Order::findOrFail($orderId);
        $content = $this->invoiceService->renderText($order);

        return view('orders.receipt-print', [
            'order'   => $order,
            'content' => $content,
        ]);
    }
}
