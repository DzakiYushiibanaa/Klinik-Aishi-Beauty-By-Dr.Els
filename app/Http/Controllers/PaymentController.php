<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Order;
use App\Services\FonnteService;
use App\Services\Members\ConsultationMembershipService;
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
            return redirect()->back()->with('error', 'Gagal memproses pembayaran. Silakan coba lagi atau hubungi admin.');
        }

        // Return View (Pastikan view ini ada: resources/views/payment/show.blade.php)
        // Kalau view kamu ada di checkout.payment, sesuaikan namanya.
        return view('payment.show', compact('order', 'snapToken'));
    }

    /**
     * API untuk mendapatkan Snap Token (AJAX)
     */
    public function getToken($orderId)
    {
        $this->setMidtransConfig();
        
        $order = Order::with(['items', 'user'])
            ->where('kode_order', $orderId)
            ->orWhere('order_id', $orderId)
            ->firstOrFail();

        if ($order->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'menunggu_pembayaran') {
            return response()->json(['error' => 'Pesanan sudah dibayar atau status tidak valid', 'reload' => true], 400);
        }

        if ($order->expired_at && now()->greaterThan($order->expired_at)) {
            $this->handlePaymentFailed($order->order_id);
            return response()->json(['error' => 'Waktu pembayaran telah habis. Pesanan dibatalkan otomatis.', 'reload' => true], 400);
        }

        try {
            if ($order->snap_token) {
                $snapToken = $order->snap_token;
            } else {
                $snapToken = $this->generateSnapToken($order);
                $order->update(['snap_token' => $snapToken]);
            }
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function generateSnapToken(Order $order): string
    {
        $user = $order->user;
        $itemDetails = [];

        foreach ($order->items as $item) {
            // Generate ID unik untuk midtrans
            $itemId = 'PRD-' . ($item->product_id ?? $item->order_item_id);

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

        if ($order->expired_at) {
            $params['expiry'] = [
                'start_time' => $order->created_at->format('Y-m-d H:i:s O'),
                'unit'       => 'minute',
                'duration'   => (int) $order->created_at->diffInMinutes($order->expired_at)
            ];
        }

        return Snap::getSnapToken($params);
    }

    /**
     * Webhook Notification Handler (Dipanggil oleh Server Midtrans)
     *
     * Endpoint ini menerima POST notification dari Midtrans setiap kali
     * ada perubahan status transaksi. Logic utama:
     * 1. Verifikasi signature key untuk keamanan
     * 2. Deteksi payment_type → payment_method
     * 3. Deteksi channel spesifik → payment_channel
     * 4. Update status order berdasarkan transaction_status
     * 5. Jika settlement/capture → set paid_at dengan waktu settlement Midtrans
     */
    public function notification(Request $request)
    {
        $this->setMidtransConfig();

        try {
            $notif = new Notification();
            $transaction = $notif->getResponse();

            // --------------------------------------------------
            // 1. VERIFIKASI SIGNATURE KEY (Keamanan)
            // --------------------------------------------------
            $serverKey      = config('midtrans.server_key');
            $orderId        = $transaction->order_id;
            $statusCode     = $transaction->status_code;
            $grossAmount    = $transaction->gross_amount;
            $signatureKey   = $transaction->signature_key ?? '';

            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($signatureKey !== $expectedSignature) {
                Log::warning('Midtrans Webhook: Invalid signature', [
                    'order_id'  => $orderId,
                    'ip'        => $request->ip(),
                ]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // --------------------------------------------------
            // 2. CARI ORDER ASLI (strip timestamp suffix)
            // --------------------------------------------------
            $transactionStatus = $transaction->transaction_status;
            $paymentType       = $transaction->payment_type;
            $fraudStatus       = $transaction->fraud_status ?? null;
            $transactionId     = $transaction->transaction_id;

            // order_id format: ORD-YYYYMMDD-0001-1714400000
            $realOrderId = preg_replace('/-[0-9]+$/', '', $orderId);
            
            // 1. Coba cari berdasarkan midtrans_order_id utuh (best match)
            $order = Order::where('midtrans_order_id', $orderId)->first();

            // 2. Jika tidak ketemu, coba cari berdasarkan kode_order (strip timestamp)
            if (!$order) {
                $order = Order::where('kode_order', $realOrderId)->first();
            }

            if (!$order) {
                Log::warning('Midtrans Webhook: Order not found', ['midtrans_order_id' => $orderId, 'parsed' => $realOrderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Idempotency: Jika sudah dibayar, skip processing
            if ($order->status === 'sudah_dibayar') {
                Log::info('Midtrans Webhook: Order already paid, skipping', ['kode_order' => $realOrderId]);
                return response()->json(['status' => 'already_processed']);
            }

            // --------------------------------------------------
            // 3. DETEKSI PAYMENT CHANNEL SPESIFIK
            // --------------------------------------------------
            $paymentChannel = $this->resolvePaymentChannel($paymentType, $transaction);

            // --------------------------------------------------
            // 4. TENTUKAN WAKTU PEMBAYARAN (dari Midtrans)
            // --------------------------------------------------
            $settlementTime = null;
            if (!empty($transaction->settlement_time)) {
                try {
                    $settlementTime = \Carbon\Carbon::parse($transaction->settlement_time);
                } catch (\Exception $e) {
                    $settlementTime = now();
                }
            } else {
                $settlementTime = now();
            }

            // --------------------------------------------------
            // 5. PROSES BERDASARKAN TRANSACTION STATUS
            // --------------------------------------------------
            Log::info('Midtrans Webhook Received', [
                'kode_order'         => $realOrderId,
                'midtrans_order_id'  => $orderId,
                'transaction_status' => $transactionStatus,
                'payment_type'       => $paymentType,
                'payment_channel'    => $paymentChannel,
                'fraud_status'       => $fraudStatus,
                'transaction_id'     => $transactionId,
                'gross_amount'       => $grossAmount,
            ]);

            if ($transactionStatus === 'capture') {
                // Capture hanya untuk Credit Card
                if ($fraudStatus === 'accept' || $fraudStatus === null) {
                    $this->setOrderPaid($order, $paymentType, $paymentChannel, $transactionId, $orderId, $settlementTime);
                } elseif ($fraudStatus === 'challenge') {
                    Log::warning('Midtrans Webhook: Fraud challenge', ['kode_order' => $realOrderId]);
                    $order->update(['status' => 'menunggu_pembayaran']);
                } else {
                    // deny
                    $this->handlePaymentFailed($order);
                }
            } elseif ($transactionStatus === 'settlement') {
                $this->setOrderPaid($order, $paymentType, $paymentChannel, $transactionId, $orderId, $settlementTime);
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $this->handlePaymentFailed($order);
            } elseif ($transactionStatus === 'pending') {
                $order->update([
                    'status'          => 'menunggu_pembayaran',
                    'payment_method'  => $paymentType,
                    'payment_channel' => $paymentChannel,
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Resolve channel pembayaran spesifik dari payload Midtrans
     *
     * @param string $paymentType  payment_type dari Midtrans (bank_transfer, echannel, qris, dll)
     * @param object $transaction  Full response object dari Midtrans Notification
     * @return string              Nama channel yang akan disimpan di DB (bca, bni, mandiri, qris, dll)
     */
    private function resolvePaymentChannel(string $paymentType, object $transaction): string
    {
        return match ($paymentType) {
            // Virtual Account → ambil nama bank dari va_numbers array
            'bank_transfer' => $this->resolveBankTransferChannel($transaction),

            // Mandiri Bill Payment (echannel)
            'echannel' => 'mandiri',

            // E-Wallets
            'gopay'      => 'gopay',
            'shopeepay'  => 'shopeepay',

            // QRIS (bisa GoPay/ShopeePay/Dana di balik layar)
            'qris' => 'qris',

            // Convenience Store
            'cstore' => strtolower($transaction->store ?? 'minimarket'),

            // Credit Card
            'credit_card' => 'credit_card',

            // Cardless Credit (Akulaku, Kredivo)
            'akulaku'  => 'akulaku',
            'kredivo'  => 'kredivo',

            // Default: gunakan payment_type apa adanya
            default => $paymentType,
        };
    }

    /**
     * Resolve bank name dari payload bank_transfer
     */
    private function resolveBankTransferChannel(object $transaction): string
    {
        // Standard VA (BCA, BNI, BRI, CIMB)
        if (isset($transaction->va_numbers[0]->bank)) {
            return strtolower($transaction->va_numbers[0]->bank);
        }

        // Permata VA (format berbeda)
        if (isset($transaction->permata_va_number)) {
            return 'permata';
        }

        return 'bank_transfer';
    }

    private function handlePaymentFailed($order)
    {
        if (in_array($order->status, ['batal'])) {
            return; // Sudah dibatalkan sebelumnya
        }

        DB::transaction(function () use ($order) {
            
            // 1. Update Order
            $order->update(['status' => 'batal']);

            // 2. Batalkan semua Appointment terkait jika type-nya treatment
            if ($order->order_type === 'treatment') {
                $appointmentIds = $order->items
                    ->pluck('appointment_id')
                    ->filter()
                    ->unique();

                if ($appointmentIds->isNotEmpty()) {
                    Appointment::whereIn('appointment_id', $appointmentIds)
                        ->whereIn('status', ['menunggu_pembayaran'])
                        ->update([
                            'status'        => 'batal',
                            'canceled_by'   => 'sistem',
                            'cancel_reason' => 'Pembayaran tidak diterima / expired.',
                        ]);

                    Log::info('Payment failed/expired, appointment cancelled', [
                        'order_id' => $order->order_id,
                    ]);
                }
            }
        });
    }

    /**
     * Logic Utama Set Order Sudah Dibayar (Dipanggil Webhook & Redirect Success)
     *
     * @param Order       $order
     * @param string      $paymentType     payment_type dari Midtrans
     * @param string      $paymentChannel  channel spesifik (bca, qris, dll)
     * @param string      $trxId           transaction_id dari Midtrans
     * @param string      $midtransOrderId order_id lengkap dari Midtrans
     * @param \Carbon\Carbon|null $paidAt  Waktu settlement dari Midtrans (null = now())
     */
    private function setOrderPaid($order, $paymentType, $paymentChannel, $trxId, $midtransOrderId, $paidAt = null)
    {
        if ($order->status !== 'sudah_dibayar') {
            DB::transaction(function () use ($order, $paymentType, $paymentChannel, $trxId, $midtransOrderId, $paidAt) {

                // 1. Update Status Order
                $order->update([
                    'status'            => 'sudah_dibayar',
                    'shipping_status'   => ($order->order_type == 'membership') ? 'selesai' : 'diproses',
                    'paid_at'           => $paidAt ?? now(),
                    'payment_method'    => $paymentType,
                    'payment_channel'   => $paymentChannel,
                    'payment_ref'       => $trxId,
                    'midtrans_order_id' => $midtransOrderId,
                ]);

                // 2. Jika order type treatment → update appointment linked ke order ini
                if ($order->order_type === 'treatment') {
                    $appointmentIds = $order->items
                        ->pluck('appointment_id')
                        ->filter()
                        ->unique();

                    if ($appointmentIds->isNotEmpty()) {
                        Appointment::whereIn('appointment_id', $appointmentIds)
                            ->whereIn('status', ['menunggu_pembayaran', 'diajukan'])
                            ->update([
                                'status'             => 'disetujui',
                                'payment_expired_at' => null, // hapus deadline karena sudah dibayar
                            ]);

                        Log::info('Appointment(s) disetujui setelah payment sukses', [
                            'order_id'        => $order->order_id,
                            'appointment_ids' => $appointmentIds->toArray(),
                        ]);
                    }
                }

                // 3. Logic Khusus Membership
                if ($order->order_type === 'membership') {
                    $pasien = \App\Models\Pasien::where('user_id', $order->user_id)->first();

                    if ($pasien) {
                        $prefix    = 'RM';
                        $digits    = 6;
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

                        $pasien->update([
                            'is_member'      => true,
                            'member_since'   => now(),
                            'no_rekam_medis' => $newKodeRM,
                        ]);

                        Log::info("Membership activated via Midtrans. User: {$order->user_id}. New RM: {$newKodeRM}");
                    }
                }

                app(ConsultationMembershipService::class)
                    ->activateIfEligibleForOrder($order->fresh(['items.consultation', 'items.appointment.items.consultation']));
            });

            // 🔔 WhatsApp ke pasien — kirim setelah transaksi commit (best-effort).
            try {
                $orderForWa = Order::with(['user.pasienProfile', 'items'])
                    ->find($order->order_id);
                if ($orderForWa) {
                    app(FonnteService::class)->sendPaymentConfirmed($orderForWa);
                }
            } catch (\Throwable $e) {
                Log::warning('Fonnte WA send gagal (payment confirmed): '.$e->getMessage());
            }
        }
    }

    /**
     * Redirect Sukses dari Midtrans
     */
    public function success(Request $request)
    {
        $orderIdFull = $request->query('order_id');
        Log::info('Payment Success Redirect', ['order_id_param' => $orderIdFull]);
        
        if ($orderIdFull) {
            $realOrderId = preg_replace('/-[0-9]+$/', '', $orderIdFull);
            $order = Order::where('kode_order', $realOrderId)
                ->orWhere('order_id', $realOrderId)
                ->first();
            
            Log::info('Payment Success Order Lookup', [
                'found' => $order ? true : false,
                'status' => $order?->status,
                'kode_order' => $order?->kode_order,
            ]);
            
            // Antisipasi Webhook telat: Set Paid manual saat user redirect balik
            if ($order && $order->status == 'menunggu_pembayaran') {
                Log::info('Calling setOrderPaid from success redirect', ['kode_order' => $order->kode_order]);
                $this->setOrderPaid(
                    $order, 
                    'midtrans_snap', 
                    'web_redirect', 
                    'waiting_webhook', 
                    $order->kode_order
                );
                Log::info('setOrderPaid completed', ['new_status' => $order->fresh()->status]);
            }
        }
        // Determine redirect tab based on order type
        $tab = ($order && $order->order_type === 'produk') ? 'orders' : 'appointment';
        $highlightId = $order ? $order->order_id : '';
        
        return redirect()->to(route('dashboard') . "?tab={$tab}&highlight={$highlightId}")
            ->with('success', 'Pembayaran berhasil. Pesanan Anda sedang diproses.');
    }

    public function unfinish(Request $request) { 
        return redirect()->to(route('dashboard') . '?tab=orders')
            ->with('warning', 'Pembayaran belum selesai. Silakan lanjutkan dari halaman pesanan.'); 
    }
    public function error(Request $request) { 
        return redirect()->to(route('dashboard') . '?tab=orders')
            ->with('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.'); 
    }
}
