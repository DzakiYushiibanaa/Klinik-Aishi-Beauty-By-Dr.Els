<?php

namespace App\Support;

use App\Models\Order;

/**
 * Formatter metode pembayaran untuk seluruh aplikasi.
 *
 * Sumber data:
 *   - Order::payment_method  : kategori utama dari Midtrans (bank_transfer, qris, gopay, dll)
 *   - Order::payment_channel : channel spesifik yang sudah di-resolve oleh
 *                              PaymentController::resolvePaymentChannel() (bca, bni, mandiri, dll)
 *
 * Contoh output:
 *   - "BCA Virtual Account"
 *   - "BNI Virtual Account"
 *   - "Mandiri Bill Payment"
 *   - "QRIS"
 *   - "GoPay"
 *   - "ShopeePay"
 *   - "Alfamart"
 *   - "Indomaret"
 *   - "Kartu Kredit"
 *   - "Belum Dipilih" (kalau order belum dibayar)
 */
class PaymentMethodFormatter
{
    /** Map channel spesifik → label human-readable. */
    private const CHANNEL_LABELS = [
        // Bank Transfer / Virtual Account
        'bca'           => 'BCA Virtual Account',
        'bni'           => 'BNI Virtual Account',
        'bri'           => 'BRI Virtual Account',
        'mandiri'       => 'Mandiri Bill Payment',
        'mandiri_bill'  => 'Mandiri Bill Payment',
        'permata'       => 'Permata Virtual Account',
        'cimb'          => 'CIMB Virtual Account',
        'bsi'           => 'BSI Virtual Account',
        'bank_transfer' => 'Transfer Bank',

        // QRIS
        'qris'          => 'QRIS',
        'midtrans_qris' => 'QRIS',

        // E-wallets
        'gopay'         => 'GoPay',
        'shopeepay'     => 'ShopeePay',
        'dana'          => 'DANA',
        'ovo'           => 'OVO',
        'linkaja'       => 'LinkAja',

        // Convenience store
        'alfamart'      => 'Alfamart',
        'indomaret'     => 'Indomaret',
        'cstore'        => 'Minimarket',

        // Card / cardless credit
        'credit_card'   => 'Kartu Kredit',
        'akulaku'       => 'Akulaku',
        'kredivo'       => 'Kredivo',

        // Other
        'cash'          => 'Tunai',
        'web_redirect'  => 'Midtrans',
        'midtrans_snap' => 'Midtrans',
    ];

    /**
     * Format payment method dari Order menjadi label readable.
     *
     * @param Order|object|null $order  Bisa juga objek lain yang punya field
     *                                  `payment_method` & `payment_channel`.
     * @param array  $opts
     *   - empty_label    : string yang dipakai kalau order belum dibayar.
     *   - status         : status order (override pengecekan empty).
     */
    public static function format(?object $order, array $opts = []): string
    {
        $emptyLabel = $opts['empty_label'] ?? 'Belum Dipilih';

        if (!$order) {
            return $emptyLabel;
        }

        $channel = strtolower((string) ($order->payment_channel ?? ''));
        $method  = strtolower((string) ($order->payment_method ?? ''));

        // 1. Channel paling spesifik — coba match dulu.
        if ($channel !== '' && isset(self::CHANNEL_LABELS[$channel])) {
            return self::CHANNEL_LABELS[$channel];
        }

        // 2. Fallback: kalau bank_transfer tapi channel kosong, pakai label generik.
        if ($method !== '' && isset(self::CHANNEL_LABELS[$method])) {
            return self::CHANNEL_LABELS[$method];
        }

        // 3. Kombinasi raw — kasih label fallback yang manusiawi.
        if (!empty($channel)) {
            return ucwords(str_replace(['_', '-'], ' ', $channel));
        }
        if (!empty($method)) {
            return ucwords(str_replace(['_', '-'], ' ', $method));
        }

        return $emptyLabel;
    }

    /**
     * Versi compact untuk badge / chip kecil (misal di tabel).
     * Mengembalikan label yang sama tapi singkat.
     */
    public static function short(?object $order, array $opts = []): string
    {
        $label = self::format($order, $opts);

        // Kalau bentuknya "BCA Virtual Account" → singkat jadi "BCA VA".
        return match (true) {
            str_ends_with($label, ' Virtual Account') => str_replace(' Virtual Account', ' VA', $label),
            $label === 'Mandiri Bill Payment'         => 'Mandiri Bill',
            default                                   => $label,
        };
    }
}
