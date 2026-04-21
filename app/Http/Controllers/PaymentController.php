<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    private function setMidtransConfig(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Tampilkan Halaman Pembayaran (Generate Snap Token)
     */
    public function show($orderId)
    {
        $this->setMidtransConfig();
        
        // Support 'kode_order' atau 'order_id'
        $order = Order::with(['items', 'user'])
            ->where('kode_order', $orderId)
            ->orWhere('order_id', $orderId)
            ->firstOrFail();

        // Security Check
        if ($order->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        // Cek Status
        if ($order->status !== 'menunggu_pembayaran') {
            return redirect()->route('dashboard')->with('info', 'Status pesanan tidak valid atau sudah dibayar.');
        }

        try {
            // Cek token yang sudah ada biar gak generate ulang terus
            if ($order->snap_token) {
                $snapToken = $order->snap_token;
            } else {
                $snapToken = $this->generateSnapToken($order);
                $order->update(['snap_token' => $snapToken]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Midtrans Error: ' . $e->getMessage());
        }

        // Return View (Pastikan view ini ada: resources/views/payment/show.blade.php)
        // Kalau view kamu ada di checkout.payment, sesuaikan namanya.
        return view('payment.show', compact('order', 'snapToken'));
    }

    private function generateSnapToken(Order $order): string
    {
        $user = $order->user;
        $itemDetails = [];

        foreach ($order->items as $item) {
            // Generate ID unik untuk midtrans
            $itemId = $item->item_type === 'bundle' 
                ? 'BUN-' . ($item->bundle_id ?? $item->order_item_id)
                : 'PRD-' . ($item->product_id ?? $item->order_item_id);

            $itemDetails[] = [
                'id'       => substr($itemId, 0, 50), // Limit length
                'price'    => (int) $item->harga_satuan,
                'quantity' => (int) $item->qty,
                'name'     => substr($item->description ?? 'Item', 0, 50),
            ];
        }

        if ($order->ongkir > 0) {
            $itemDetails[] = ['id' => 'ONGKIR', 'price' => (int) $order->ongkir, 'quantity' => 1, 'name' => 'Ongkos Kirim'];
        }

        if ($order->diskon_total > 0) {
            $itemDetails[] = ['id' => 'DISKON', 'price' => -(int) $order->diskon_total, 'quantity' => 1, 'name' => 'Diskon Promo'];
        }

        // Tambahkan timestamp agar order_id unik jika terjadi regenerasi token
        $customOrderId = $order->kode_order . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $customOrderId, 
                'gross_amount' => (int) $order->grand_total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '08123456789'
            ],
        ];

        return Snap::getSnapToken($params);
    }

    /**
     * Webhook Notification Handler (Dipanggil Midtrans)
     */
    public function notification(Request $request)
    {
        $this->setMidtransConfig();
        
        try {
            $notif = new Notification();
            
            $transaction = $notif->getResponse();
            $transactionStatus = $notif->transaction_status;
            $type = $notif->payment_type;
            $fraud = $notif->fraud_status;
            $orderIdFull = $notif->order_id; // ORD-xxxx-timestamp

            // Cari Order Asli (Hapus Suffix Timestamp)
            $realOrderId = preg_replace('/-[0-9]+$/', '', $orderIdFull);
            $order = Order::where('kode_order', $realOrderId)->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Deteksi Channel Pembayaran
            $paymentChannel = $type; 
            if ($type == 'bank_transfer') {
                if (isset($transaction->va_numbers[0]->bank)) {
                    $paymentChannel = $transaction->va_numbers[0]->bank;
                } elseif (isset($transaction->permata_va_number)) {
                    $paymentChannel = 'permata';
                }
            } elseif ($type == 'echannel') {
                $paymentChannel = 'mandiri_bill';
            } elseif ($type == 'qris') {
                $paymentChannel = 'qris';
            } else if ($type == 'cstore') {
                $paymentChannel = $transaction->store ?? 'alfamart/indomaret';
            }

            // Proses Status
            if ($transactionStatus == 'capture') {
                if ($fraud == 'challenge') {
                    // Challenge
                } else if ($fraud == 'accept') {
                    $this->setOrderPaid($order, $type, $paymentChannel, $notif->transaction_id, $orderIdFull);
                }
            } else if ($transactionStatus == 'settlement') {
                $this->setOrderPaid($order, $type, $paymentChannel, $notif->transaction_id, $orderIdFull);
            } else if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $order->update(['status' => 'batal']);
            } else if ($transactionStatus == 'pending') {
                $order->update(['status' => 'menunggu_pembayaran']);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Logic Utama Set Order Lunas (Dipanggil Webhook & Redirect Success)
     */
    private function setOrderPaid($order, $paymentType, $paymentChannel, $trxId, $midtransOrderId)
    {
        if ($order->status !== 'sudah_dibayar' && $order->status !== 'lunas') {
            DB::transaction(function () use ($order, $paymentType, $paymentChannel, $trxId, $midtransOrderId) {
                
                // 1. Update Status Order
                $order->update([
                    'status'            => 'sudah_dibayar',
                    'shipping_status'   => ($order->order_type == 'membership') ? 'selesai' : 'diproses',
                    'paid_at'           => now(),
                    'payment_method'    => $paymentType,
                    'payment_channel'   => $paymentChannel,
                    'payment_ref'       => $trxId,
                    'midtrans_order_id' => $midtransOrderId,
                ]);

                // 2. Logic Khusus Membership
                if ($order->order_type === 'membership') {
                    $pasien = \App\Models\Pasien::where('user_id', $order->user_id)->first();
                    
                    if ($pasien) {
                        // Generate No RM Baru
                        $prefix = 'RM';
                        $digits = 6;
                        $separator = '-';

                        $latestRM = \App\Models\Pasien::where('no_rekam_medis', 'like', $prefix . $separator . '%')
                            ->orderByDesc('no_rekam_medis')
                            ->value('no_rekam_medis');

                        $nextNumber = 1;
                        if ($latestRM) {
                            $numberPart = substr($latestRM, -(int) $digits);
                            $nextNumber = ((int) $numberPart) + 1;
                        }

                        $newKodeRM = $prefix . $separator . str_pad((string) $nextNumber, $digits, '0', STR_PAD_LEFT);

                        // Update Profil Pasien
                        $pasien->update([
                            'is_member'      => true,
                            'member_since'   => now(),
                            'no_rekam_medis' => $newKodeRM 
                        ]);
                        
                        Log::info("Membership activated via Midtrans. User: {$order->user_id}. New RM: {$newKodeRM}");
                    }
                }
            });
        }
    }

    /**
     * Redirect Sukses dari Midtrans
     */
    public function success(Request $request)
    {
        $orderId = $request->query('order_id');
        
        if ($orderId) {
            $order = Order::where('kode_order', $orderId)->first();
            
            // Antisipasi Webhook telat: Set Paid manual saat user redirect balik
            if ($order && $order->status == 'menunggu_pembayaran') {
                $this->setOrderPaid(
                    $order, 
                    'midtrans_snap', 
                    'web_redirect', 
                    'waiting_webhook', 
                    $order->kode_order
                );
            }
        }
        
        return redirect()->route('dashboard')->with('success', 'Pembayaran Berhasil! Pesanan Anda sedang diproses.');
    }

    public function unfinish() { return redirect()->route('dashboard')->with('warning', 'Pembayaran belum selesai.'); }
    public function error() { return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan pembayaran.'); }
}