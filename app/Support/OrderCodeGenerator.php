<?php

namespace App\Support;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Generator kode order yang konsisten di seluruh aplikasi.
 *
 * Format: {PREFIX}-{YYYYMMDD}-{XXX}
 * Contoh: ORD-20260509-001, MEM-20260509-002
 *
 * - Reset sekuens setiap hari.
 * - Default 3 digit (sesuai standar nota klinik).
 * - Otomatis melebar ke 4+ digit jika dalam 1 hari ada >999 transaksi
 *   (jarang, tapi tetap aman: tidak akan tabrakan unique constraint).
 * - Tahan terhadap data lama berformat 4 digit (membaca MAX numerik
 *   apa pun panjangnya).
 */
class OrderCodeGenerator
{
    public const PREFIX_ORDER      = 'ORD';
    public const PREFIX_MEMBERSHIP = 'MEM';

    /**
     * Generate kode order baru.
     *
     * @param string $prefix Prefix kode (ORD / MEM / dll). Default ORD.
     * @param Carbon|null $now Waktu acuan (untuk testing). Default now().
     * @param int $minDigits Minimal panjang sekuens. Default 3.
     */
    public static function next(string $prefix = self::PREFIX_ORDER, ?Carbon $now = null, int $minDigits = 3): string
    {
        $now ??= now();
        $date = $now->format('Ymd');
        $like = "{$prefix}-{$date}-%";

        // Cari sekuens tertinggi hari ini, terlepas panjang digitnya.
        // Pakai lock pessimistic supaya saat dipakai dalam DB::transaction(),
        // race condition antar request bisa dicegah.
        $query = Order::query()
            ->where('kode_order', 'like', $like);

        if (DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $existing = $query->pluck('kode_order');
        $max      = 0;
        $stripLen = strlen($prefix) + 1 + strlen($date) + 1; // PREFIX-YYYYMMDD-

        foreach ($existing as $kode) {
            $tail = substr((string) $kode, $stripLen);
            if (ctype_digit($tail)) {
                $val = (int) $tail;
                if ($val > $max) $max = $val;
            }
        }

        $next = $max + 1;

        return sprintf('%s-%s-%s', $prefix, $date, str_pad((string) $next, $minDigits, '0', STR_PAD_LEFT));
    }
}
