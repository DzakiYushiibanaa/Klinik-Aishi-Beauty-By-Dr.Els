<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Consultation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RekamMedis;
use App\Models\Treatment;
use App\Notifications\AppointmentCompleted;
use App\Services\Members\ConsultationMembershipService;
use App\Services\Orders\ProductStockService;
use App\Support\CodeGenerator;
use App\Support\TreatmentCommissionCalculator;
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
        // Validasi: setiap item treatment wajib punya handler.
        // Bundle tetap optional supaya tidak memblokir flow lama.
        $this->assertHandlerSelected($data);

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

            app(ConsultationMembershipService::class)
                ->activateIfEligibleForOrder($order->fresh(['items.consultation', 'items.appointment.items.consultation']));

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
     * Pastikan setiap baris treatment di form sudah memilih staff handler.
     * Kalau ada yang belum, lempar exception agar Filament action menampilkan
     * pesan validasi ke admin.
     */
    protected function assertHandlerSelected(array $data): void
    {
        $rows = (array) ($data['treatment_items'] ?? []);

        foreach ($rows as $row) {
            $type = $row['item_type'] ?? null;
            if ($type !== 'treatment') continue; // bundle/lain optional.

            // Treatment id boleh kosong di repeater (row baru belum diisi);
            // kalau tidak ada treatment_id, anggap row akan di-skip oleh syncAppointmentItems.
            if (empty($row['treatment_id'])) continue;

            if (empty($row['handled_by_type']) || empty($row['handled_by_id'])) {
                $treatment = Treatment::with('category')->find($row['treatment_id']);
                $allowed = $treatment?->category?->allowedHandlerTypes() ?? ['dokter', 'terapis'];

                throw new \RuntimeException(
                    $allowed === ['dokter']
                        ? 'Pilih dokter yang menangani treatment ini.'
                        : 'Pilih dokter/terapis yang menangani treatment ini.'
                );
            }
        }
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
            $appointment->refresh()->load('items');

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
                // Konsultasi tidak masuk skema komisi treatment.
                'handled_by_type'    => 'dokter',
                'handled_by_id'      => $appointment->dokter_id,
                'commission_percent' => 0,
                'commission_amount'  => 0,
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

            // Buat item dulu lalu hitung komisi via calculator (butuh
            // relasi treatment/bundle yang ter-load).
            $item = $appointment->items()->create([
                'item_type'    => $type,
                'treatment_id' => $type === 'treatment' ? (int) $row['treatment_id'] : null,
                'bundle_id'    => $type === 'bundle' ? (int) $row['bundle_id'] : null,
                'qty'          => max(1, (int) ($row['qty'] ?? 1)),
                'handled_by_type' => $row['handled_by_type'] ?? null,
                'handled_by_id'   => isset($row['handled_by_id']) && $row['handled_by_id'] !== ''
                    ? (int) $row['handled_by_id']
                    : null,
                'commission_percent' => TreatmentCommissionCalculator::DEFAULT_PERCENT,
                'commission_amount'  => 0, // diisi setelah relasi ter-load.
            ]);

            // Hitung & persist nominal komisi.
            $item->loadMissing(['treatment', 'bundle']);
            $amount = TreatmentCommissionCalculator::forItem($item);
            if ($item->commission_amount !== $amount) {
                $item->commission_amount = $amount;
                $item->save();
            }
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
            'terapis_id'          => $this->resolveTerapisIdForMedicalRecord($appointment),
            'keluhan'             => $data['keluhan'] ?? null,
            'diagnosa'            => $data['diagnosa'] ?? null,
            'tindakan'            => $tindakanText,
            'produk_rekomendasi' => $regimenText,
            'catatan_lanjutan'    => $data['catatan_lanjutan'] ?? null,
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
    protected function resolveTerapisIdForMedicalRecord(Appointment $appointment): ?int
    {
        $handlerId = $appointment->items
            ?->first(fn ($item) => strtolower((string) $item->handled_by_type) === 'terapis' && ! empty($item->handled_by_id))
            ?->handled_by_id;

        return $handlerId ? (int) $handlerId : ($appointment->terapis_id ? (int) $appointment->terapis_id : null);
    }

    protected function createOrder(Appointment $appointment, array $data): Order
    {
        $items = [];

        // (a) Konsultasi
        if ($appointment->jenis === 'konsultasi') {
            $consultationId = (int) ($data['consultation_id'] ?? 0);
            if ($consultationId && $c = Consultation::find($consultationId)) {
                $items[] = [
                    'item_type'       => 'consultation',
                    'treatment_id'    => null,
                    'bundle_id'       => null,
                    'product_id'      => null,
                    'consultation_id' => $consultationId,
                    'description'     => $c->nama,
                    'qty'             => 1,
                    'harga_satuan'    => (int) $c->harga,
                    'subtotal'        => (int) $c->harga,
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

        $isPaid = ($data['payment_status'] ?? 'menunggu_pembayaran') === 'sudah_dibayar';

        $order = Order::create([
            'user_id'         => $appointment->user_id,
            'kode_order'      => $kodeOrder,
            'order_type'      => $appointment->jenis === 'konsultasi' ? 'konsultasi' : 'treatment',
            'sumber'          => 'offline',
            'dibuat_oleh_id'  => auth()->id() ?? $appointment->dibuat_oleh_id ?? $appointment->user_id,
            'status'          => $isPaid ? 'sudah_dibayar' : 'menunggu_pembayaran',
            'payment_method'  => $data['payment_method'] ?? null,
            'subtotal'        => $subtotal,
            'diskon_total'    => 0,
            'ongkir'          => 0,
            'grand_total'     => $grand_total,
            'shipping_status' => 'belum_dikirim',
            'paid_at'         => $isPaid ? now() : null,
        ]);

        foreach ($items as $row) {
            OrderItem::create([
                'order_id'        => $order->order_id,
                'appointment_id'  => $appointment->appointment_id,
                'product_id'      => $row['product_id'],
                'treatment_id'    => $row['treatment_id'],
                'bundle_id'       => $row['bundle_id'],
                'consultation_id' => $row['consultation_id'] ?? null,
                'item_type'       => $row['item_type'],
                'description'     => $row['description'],
                'qty'             => $row['qty'],
                'harga_satuan'    => $row['harga_satuan'],
                'diskon'          => 0,
                'subtotal'        => $row['subtotal'],
            ]);
        }

        app(ProductStockService::class)->deductForOrder($order->fresh('items'));

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
        return \App\Support\OrderCodeGenerator::next();
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
