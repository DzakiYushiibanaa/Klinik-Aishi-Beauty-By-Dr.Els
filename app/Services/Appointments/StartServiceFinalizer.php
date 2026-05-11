<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Consultation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RekamMedis;
use App\Notifications\AppointmentCompleted;
use App\Support\CodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Finalizes an appointment "Mulai Layanan" workflow:
 *
 * 1. Syncs the appointment's linked items (konsultasi + treatments/bundles from form).
 * 2. Creates/updates the medical record (Rekam Medis) from the form data.
 * 3. Creates an Order (transaksi) that bundles all appointment items + any extra products.
 * 4. Marks the appointment as selesai.
 * 5. Notifies the customer (DB bell + email) with an invoice-style summary.
 *
 * Designed to be called from the Filament "Mulai Layanan" action with a single data payload.
 */
class StartServiceFinalizer
{
    /**
     * Completed mode — buat rekam medis + order + notifikasi.
     *
     * @param  array<string,mixed>  $data  Data dari Filament action form
     */
    public function complete(Appointment $appointment, array $data): array
    {
        return DB::transaction(function () use ($appointment, $data) {
            // 1. Pastikan appointment_items konsisten dengan input form.
            $this->syncAppointmentItems($appointment, $data);
            $appointment->refresh()->load(['items.treatment', 'items.bundle', 'items.consultation']);

            // 2. Rekam medis (upsert per appointment)
            $rekamMedis = $this->upsertRekamMedis($appointment, $data);

            // 3. Order (transaksi) — gabungkan item appointment + produk tambahan
            $order = $this->createOrder($appointment, $data);

            // 4. Update appointment status
            $appointment->update([
                'status' => 'selesai',
            ]);

            // 5. Notifikasi ke customer
            $this->notifyCustomer($appointment->fresh(), $order);

            return [
                'appointment' => $appointment->fresh(),
                'rekam_medis' => $rekamMedis,
                'order'       => $order,
            ];
        });
    }

    /**
     * Simpan progress tanpa finalisasi (status belum selesai, tidak ada order, tidak notif).
     *
     * Hanya merge rekam medis draft dan appointment_items baru supaya
     * dokter/terapis bisa kembali melanjutkan nanti.
     */
    public function saveDraft(Appointment $appointment, array $data): array
    {
        return DB::transaction(function () use ($appointment, $data) {
            $this->syncAppointmentItems($appointment, $data);

            $rekamMedis = $this->upsertRekamMedis($appointment, $data, asDraft: true);

            return [
                'appointment' => $appointment->fresh(),
                'rekam_medis' => $rekamMedis,
            ];
        });
    }

    // ───────────────────────────────────────────────────────
    // STEP 1: sync appointment items
    // ───────────────────────────────────────────────────────
    protected function syncAppointmentItems(Appointment $appointment, array $data): void
    {
        // Consultation — hanya relevan jika jenis=konsultasi
        if ($appointment->jenis === 'konsultasi' && ! empty($data['consultation_id'])) {
            $appointment->items()->where('item_type', 'consultation')->delete();
            $appointment->items()->create([
                'item_type'       => 'consultation',
                'consultation_id' => (int) $data['consultation_id'],
                'qty'             => 1,
            ]);
        }

        // Treatment / Bundle — the form always submits the full list (default-loaded from DB),
        // so we mirror it back: delete existing follow-ups then recreate from submitted rows.
        // This lets users add OR remove items via the modal.
        $follow = array_values(array_filter((array) ($data['treatment_items'] ?? []), function ($row) {
            $type = $row['item_type'] ?? null;
            return ($type === 'treatment' && ! empty($row['treatment_id']))
                || ($type === 'bundle' && ! empty($row['bundle_id']));
        }));

        $appointment->items()->whereIn('item_type', ['treatment', 'bundle'])->delete();

        foreach ($follow as $row) {
            $type = $row['item_type'];
            $appointment->items()->create([
                'item_type'    => $type,
                'treatment_id' => $type === 'treatment' ? (int) $row['treatment_id'] : null,
                'bundle_id'    => $type === 'bundle' ? (int) $row['bundle_id'] : null,
                'qty'          => max(1, (int) ($row['qty'] ?? 1)),
            ]);
        }
    }

    // ───────────────────────────────────────────────────────
    // STEP 2: rekam medis upsert
    // ───────────────────────────────────────────────────────
    protected function upsertRekamMedis(Appointment $appointment, array $data, bool $asDraft = false): RekamMedis
    {
        // Format: KJ-000001 — mengikuti pola kode rekam medis existing di database.
        $noKunjungan = CodeGenerator::prefixNumber(new RekamMedis(), 'no_kunjungan', 'KJ', 6, '-');

        $tindakanText = $this->resolveTindakanText($appointment);

        // Regimen → render jadi teks ringkas untuk kolom `produk_rekomendasi`
        $regimenText = $this->resolveRegimenText($data);

        $payload = [
            'appointment_id'      => $appointment->appointment_id,
            'pasien_id'           => $appointment->user_id,
            'dokter_id'           => $appointment->dokter_id,
            'terapis_id'          => $appointment->terapis_id,
            'keluhan'             => $data['keluhan'] ?? null,
            'diagnosa'            => $data['diagnosa'] ?? null,
            'tindakan'            => $tindakanText,
            'produk_rekomendasi' => $regimenText,
            'catatan_lanjutan'    => $data['catatan_lanjutan'] ?? null,
            'status_tindakan'     => $asDraft ? 'direncanakan' : 'dilakukan',
        ];

        // Upload foto sebelum/sesudah — Filament FileUpload sudah memindahkan ke disk.
        // Nilainya bisa berupa string (single) atau array dari TemporaryUploadedFile → setelah save jadi path string.
        if (array_key_exists('foto_sebelum_path', $data)) {
            $payload['foto_sebelum_path'] = $this->normalizeFilamentFile($data['foto_sebelum_path']);
        }
        if (array_key_exists('foto_sesudah_path', $data)) {
            $payload['foto_sesudah_path'] = $this->normalizeFilamentFile($data['foto_sesudah_path']);
        }

        /** @var RekamMedis $rm */
        $rm = RekamMedis::firstOrNew(['appointment_id' => $appointment->appointment_id]);

        if (! $rm->exists) {
            $rm->no_kunjungan = $noKunjungan;
        }

        $rm->fill($payload)->save();

        return $rm;
    }

    protected function resolveTindakanText(Appointment $appointment): ?string
    {
        $items = $appointment->items()
            ->whereIn('item_type', ['treatment', 'bundle'])
            ->with(['treatment', 'bundle'])
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        return $items->map(function (AppointmentItem $it) {
            $name = $it->treatment?->nama
                ?? ($it->bundle ? 'Bundle: ' . $it->bundle->nama : null);
            $qty = (int) ($it->qty ?? 1);
            return $name ? ($name . ($qty > 1 ? " x{$qty}" : '')) : null;
        })->filter()->implode(', ');
    }

    /**
     * Render the regimen repeater rows into a human-readable string for the
     * rekam_medis.produk_rekomendasi column (legacy text field).
     */
    protected function resolveRegimenText(array $data): ?string
    {
        $rows = $this->normalizeRegimenRows($data);
        if (empty($rows)) {
            // Fallback: if legacy text field was provided directly, keep it.
            $legacy = $data['produk_rekomendasi'] ?? null;
            return is_string($legacy) && $legacy !== '' ? $legacy : null;
        }

        return collect($rows)->map(function ($row) {
            $product = Product::find($row['product_id']);
            if (! $product) return null;
            $qty = max(1, (int) ($row['qty'] ?? 1));
            return $product->nama . ($qty > 1 ? " x{$qty}" : '');
        })->filter()->implode(', ');
    }

    /**
     * Extract valid rows from the regimen repeater.
     *
     * @return array<int, array{product_id:int, qty:int}>
     */
    protected function normalizeRegimenRows(array $data): array
    {
        $rows = (array) ($data['produk_rekomendasi_items'] ?? []);
        return array_values(array_filter(array_map(function ($row) {
            $productId = (int) ($row['product_id'] ?? 0);
            if (! $productId) return null;
            return [
                'product_id' => $productId,
                'qty'        => max(1, (int) ($row['qty'] ?? 1)),
            ];
        }, $rows)));
    }

    protected function normalizeFilamentFile($value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_array($value)) {
            $first = reset($value);
            return is_string($first) && $first !== '' ? $first : null;
        }

        return null;
    }

    // ───────────────────────────────────────────────────────
    // STEP 3: create order
    // ───────────────────────────────────────────────────────
    protected function createOrder(Appointment $appointment, array $data): Order
    {
        $items = [];

        // (a) Konsultasi
        if ($appointment->jenis === 'konsultasi') {
            $consultationId = (int) ($data['consultation_id'] ?? 0);
            if ($consultationId && $c = Consultation::find($consultationId)) {
                $items[] = [
                    'item_type'    => 'consultation',
                    'treatment_id' => null,
                    'bundle_id'    => null,
                    'product_id'   => null,
                    'description'  => $c->nama,
                    'qty'          => 1,
                    'harga_satuan' => (int) $c->harga,
                    'subtotal'     => (int) $c->harga,
                ];
            }
        }

        // (b) Treatment / Bundle dari appointment_items (sudah di-sync di step 1)
        foreach ($appointment->items()->whereIn('item_type', ['treatment', 'bundle'])->with(['treatment', 'bundle'])->get() as $it) {
            $qty = max(1, (int) ($it->qty ?? 1));

            if ($it->item_type === 'treatment' && $it->treatment) {
                $price = (int) $it->treatment->harga;
                $items[] = [
                    'item_type'    => 'treatment',
                    'treatment_id' => $it->treatment_id,
                    'bundle_id'    => null,
                    'product_id'   => null,
                    'description'  => $it->treatment->nama,
                    'qty'          => $qty,
                    'harga_satuan' => $price,
                    'subtotal'     => $price * $qty,
                ];
            } elseif ($it->item_type === 'bundle' && $it->bundle) {
                $price = (int) ($it->bundle->harga_promo ?: $it->bundle->harga_normal);
                $items[] = [
                    'item_type'    => 'bundle',
                    'treatment_id' => null,
                    'bundle_id'    => $it->bundle_id,
                    'product_id'   => null,
                    'description'  => 'Bundle: ' . $it->bundle->nama,
                    'qty'          => $qty,
                    'harga_satuan' => $price,
                    'subtotal'     => $price * $qty,
                ];
            }
        }

        // (c) Produk regimen / rekomendasi (ditagih sebagai item produk)
        foreach ($this->normalizeRegimenRows($data) as $row) {
            $product = Product::find($row['product_id']);
            if (! $product) continue;
            $qty = $row['qty'];
            $price = (int) $product->harga;
            $items[] = [
                'item_type'    => 'product',
                'treatment_id' => null,
                'bundle_id'    => null,
                'product_id'   => $row['product_id'],
                'description'  => $product->nama . ' (regimen)',
                'qty'          => $qty,
                'harga_satuan' => $price,
                'subtotal'     => $price * $qty,
            ];
        }

        $subtotal    = array_sum(array_column($items, 'subtotal'));
        $grand_total = $subtotal; // no discount/ongkir in offline flow

        $kodeOrder = $this->generateKodeOrder();

        $order = Order::create([
            'user_id'         => $appointment->user_id,
            'kode_order'      => $kodeOrder,
            'order_type'      => $appointment->jenis === 'konsultasi' ? 'konsultasi' : 'treatment',
            'sumber'          => 'offline',
            'dibuat_oleh_id'  => auth()->id() ?? $appointment->dibuat_oleh_id ?? $appointment->user_id,
            'status'          => ($data['payment_status'] ?? 'menunggu_pembayaran') === 'lunas'
                ? 'lunas'
                : 'menunggu_pembayaran',
            'payment_method'  => $data['payment_method'] ?? null,
            'subtotal'        => $subtotal,
            'diskon_total'    => 0,
            'ongkir'          => 0,
            'grand_total'     => $grand_total,
            'shipping_status' => 'belum_dikirim',
            'paid_at'         => ($data['payment_status'] ?? 'menunggu_pembayaran') === 'lunas' ? now() : null,
        ]);

        foreach ($items as $row) {
            OrderItem::create([
                'order_id'       => $order->order_id,
                'appointment_id' => $appointment->appointment_id,
                'product_id'     => $row['product_id'],
                'treatment_id'   => $row['treatment_id'],
                'bundle_id'      => $row['bundle_id'],
                'item_type'      => $row['item_type'],
                'description'    => $row['description'],
                'qty'            => $row['qty'],
                'harga_satuan'   => $row['harga_satuan'],
                'diskon'         => 0,
                'subtotal'       => $row['subtotal'],
            ]);
        }

        return $order->fresh('items');
    }

    /**
     * Generate order code using the same format as CheckoutController:
     * ORD-YYYYMMDD-XXXX (sequential per day).
     *
     * Example: ORD-20260510-0008
     */
    protected function generateKodeOrder(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "ORD-{$date}-";

        $last = Order::where('kode_order', 'like', $prefix . '%')
            ->orderByDesc('order_id')
            ->value('kode_order');

        $next = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    // ───────────────────────────────────────────────────────
    // STEP 4: notify customer
    // ───────────────────────────────────────────────────────
    protected function notifyCustomer(Appointment $appointment, Order $order): void
    {
        if (! $appointment->pasien) return;

        try {
            $appointment->pasien->notify(new AppointmentCompleted($appointment, $order));
        } catch (\Throwable $e) {
            // Don't block the flow if mail transport fails; DB notif may still land.
            Log::warning('Failed sending AppointmentCompleted notification', [
                'appointment_id' => $appointment->appointment_id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
