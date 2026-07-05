@php
    /**
     * Invoice slide-over — mirrors resources/views/pdf/invoice.blade.php design
     * but adapted for Filament's slide-over context.
     */

    $statusClass = 'status-paid';
    $statusText = 'SUDAH DIBAYAR';
    if (in_array($order->status ?? '', ['batal', 'dibatalkan']) || ($order->shipping_status ?? '') === 'dibatalkan') {
        $statusClass = 'status-batal';
        $statusText = 'DIBATALKAN';
    } elseif (in_array($order->status ?? '', ['menunggu_pembayaran', 'pending', 'draft'])) {
        $statusClass = 'status-menunggu';
        $statusText = 'MENUNGGU PEMBAYARAN';
    }

    /* ── Payment label (lebih spesifik & reusable) ──────────────────── */
    $paymentLabel = \App\Support\PaymentMethodFormatter::format($order, [
        'empty_label' => '—',
    ]);

    /* ── Helper format Rupiah ───────────────────────────────────────── */
    $fmtRp = fn ($n) => 'Rp ' . number_format((int) ($n ?? 0), 0, ',', '.');

    /* ── Resolve nomor telepon (filter blank / "-") ─────────────────── */
    $phoneCandidates = [
        $order->pasien?->phone,
        $order->pasien?->pasienProfile?->no_telepon,
    ];
    $phone = collect($phoneCandidates)
        ->map(fn ($v) => trim((string) $v))
        ->first(fn ($v) => $v !== '' && $v !== '-');

    /* ── Resolve alamat lengkap (prefer shipping_address, fallback ke profil) ── */
    $needsShippingAddress = $order->items->isNotEmpty()
        && $order->items->every(fn ($item) => ! empty($item->product_id));

    $profile = $order->pasien?->pasienProfile;
    $fullAddress = trim((string) ($order->shipping_address ?? ''));
    if ($fullAddress === '' && $profile) {
        $parts = array_filter([
            $profile->alamat,
            $profile->district_name ?? null,
            $profile->kota ?? null,
            $profile->provinsi ?? null,
            $profile->kode_pos ?? null,
        ], fn ($p) => trim((string) $p) !== '');
        $fullAddress = trim(implode(', ', $parts));
    }
@endphp

<div class="fi-invoice-slideover" style="padding: 4px 2px; font-family: 'Plus Jakarta Sans', sans-serif;">
    {{-- HEADER --}}
    <div style="text-align:center; margin-bottom:24px; padding-bottom:16px; border-bottom:2px solid #B8956F;">
        <div style="font-size:22px; font-weight:700; color:#B8956F; letter-spacing:0.05em;">AISHI BEAUTY</div>
        <div style="color:#8C7A63; font-size:11px; margin-top:4px;">Klinik Aishi Beauty By Dr. Els</div>
        <div style="color:#8C7A63; font-size:11px;">Jl. Nusantara Raya No.99b, Beji, Kec. Beji, Kota Depok, Jawa Barat 16421</div>
    </div>

    {{-- DETAIL PELANGGAN + DETAIL TRANSAKSI (grid 2 kolom) --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">
        <div>
            <div style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px; letter-spacing:0.08em;">
                Detail Pelanggan
            </div>
            <div style="font-size:14px; font-weight:600; color:#111827;">
                {{ $order->pasien->name ?? '-' }}
            </div>
            @if ($order->pasien?->pasienProfile?->no_rekam_medis)
                <div style="font-size:12px; color:#64748b; margin-top:2px;">
                    RM: {{ $order->pasien->pasienProfile->no_rekam_medis }}
                </div>
            @endif
            @if ($order->pasien?->email)
                <div style="font-size:12px; color:#64748b; margin-top:2px;">
                    {{ $order->pasien->email }}
                </div>
            @endif
            @if ($phone)
                <div style="font-size:12px; color:#64748b; margin-top:2px;">
                    {{ $phone }}
                </div>
            @else
                <div style="font-size:12px; color:#94a3b8; margin-top:2px; font-style:italic;">
                    Nomor HP belum diisi
                </div>
            @endif
        </div>

        <div style="text-align:right;">
            <div style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px; letter-spacing:0.08em;">
                Detail Transaksi
            </div>
            <div style="font-size:12px; color:#111827; margin-bottom:4px;">
                Kode: <span style="font-weight:700;">{{ $order->kode_order }}</span>
            </div>
            <div style="font-size:12px; color:#111827; margin-bottom:4px;">
                Tanggal: <span style="font-weight:700;">
                    {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}
                </span>
            </div>
            <div style="font-size:12px; color:#111827; margin-bottom:8px;">
                Metode: <span style="font-weight:700;">{{ $paymentLabel }}</span>
            </div>
            @php
                $badgeStyles = [
                    'status-paid' => 'background:#d1fae5; color:#047857;',
                    'status-menunggu' => 'background:#fef3c7; color:#b45309;',
                    'status-batal' => 'background:#ffe4e6; color:#be123c;',
                ];
                $badgeStyle = $badgeStyles[$statusClass] ?? $badgeStyles['status-menunggu'];
            @endphp
            <div>
                <span style="display:inline-block; padding:4px 10px; border-radius:6px; font-size:10px; font-weight:700; text-transform:uppercase; {{ $badgeStyle }}">
                    {{ $statusText }}
                </span>
            </div>

            @if ($order->shipping_method)
                <div style="font-size:12px; color:#111827; margin-top:10px;">
                    Kurir: <span style="font-weight:700;">{{ strtoupper($order->shipping_method) }}</span>
                </div>
                @if ($order->shipping_waybill)
                    <div style="font-size:12px; color:#111827;">
                        Resi: <span style="font-weight:700;">{{ $order->shipping_waybill }}</span>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ALAMAT PENGIRIMAN: tampil hanya untuk order yang berisi produk fisik --}}
    @if ($needsShippingAddress && $fullAddress !== '')
        <div style="margin-bottom:20px; padding:12px 14px; background:#FAF8F5; border:1px solid #EDE8E3; border-radius:10px;">
            <div style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                Alamat Pengiriman
            </div>
            <div style="font-size:13px; font-weight:700; color:#111827; margin-bottom:2px;">
                {{ $order->pasien->name ?? '-' }}
            </div>
            @if ($phone)
                <div style="font-size:11px; color:#94a3b8; margin-bottom:6px;">{{ $phone }}</div>
            @endif
            <div style="font-size:12px; color:#475569; line-height:1.6;">
                {{ $fullAddress }}
            </div>
        </div>
    @endif

    {{-- TABLE ITEMS --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:12px;">
        <thead>
            <tr style="background-color:#F5F0EB;">
                <th style="padding:10px; text-align:left; font-size:10px; text-transform:uppercase; color:#64748b; letter-spacing:0.05em; border-bottom:1px solid #E8E0D8;">
                    Produk / Treatment
                </th>
                <th style="padding:10px; text-align:center; font-size:10px; text-transform:uppercase; color:#64748b; letter-spacing:0.05em; border-bottom:1px solid #E8E0D8; width:50px;">
                    Qty
                </th>
                <th style="padding:10px; text-align:right; font-size:10px; text-transform:uppercase; color:#64748b; letter-spacing:0.05em; border-bottom:1px solid #E8E0D8; width:90px;">
                    Harga
                </th>
                <th style="padding:10px; text-align:right; font-size:10px; text-transform:uppercase; color:#64748b; letter-spacing:0.05em; border-bottom:1px solid #E8E0D8; width:100px;">
                    Subtotal
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($order->items as $item)
                @php
                    $itemName = $item->product->nama
                        ?? $item->treatment->nama
                        ?? $item->description
                        ?? 'Item';

                    $lineSubtotal = $item->subtotal ?? ((int) $item->harga_satuan * (int) $item->qty);
                    $hasDiskon = ($item->diskon ?? 0) > 0;
                @endphp
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #E8E0D8;">
                        <span style="font-weight:600; color:#111827;">{{ $itemName }}</span>
                        @if ($hasDiskon)
                            <div style="font-size:10px; color:#DC2626; margin-top:2px;">
                                Diskon: -{{ $fmtRp($item->diskon) }}
                            </div>
                        @endif
                    </td>
                    <td style="padding:10px; text-align:center; border-bottom:1px solid #E8E0D8; color:#111827;">
                        {{ $item->qty }}
                    </td>
                    <td style="padding:10px; text-align:right; border-bottom:1px solid #E8E0D8; color:#111827;">
                        {{ $fmtRp($item->harga_satuan) }}
                    </td>
                    <td style="padding:10px; text-align:right; border-bottom:1px solid #E8E0D8; font-weight:700; color:#111827;">
                        {{ $fmtRp($lineSubtotal) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:20px; text-align:center; color:#94a3b8; border-bottom:1px solid #E8E0D8;">
                        Tidak ada item
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SUMMARY --}}
    <table style="width:100%; margin-top:8px; font-size:12px;">
        <tr>
            <td style="padding:4px 0; color:#64748b; width:70%;">Subtotal</td>
            <td style="padding:4px 0; text-align:right; font-weight:700; color:#111827;">
                {{ $fmtRp($order->subtotal) }}
            </td>
        </tr>
        @if (($order->diskon_total ?? 0) > 0)
            <tr>
                <td style="padding:4px 0; color:#64748b;">Diskon Promo</td>
                <td style="padding:4px 0; text-align:right; font-weight:700; color:#be123c;">
                    -{{ $fmtRp($order->diskon_total) }}
                </td>
            </tr>
        @endif
        @if (($order->ongkir ?? 0) > 0)
            <tr>
                <td style="padding:4px 0; color:#64748b;">Ongkos Kirim</td>
                <td style="padding:4px 0; text-align:right; font-weight:700; color:#111827;">
                    {{ $fmtRp($order->ongkir) }}
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="2" style="padding:14px 0 0; border-top:1px dashed #E8E0D8;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="text-transform:uppercase; font-size:11px; color:#64748b; letter-spacing:0.1em; font-weight:600;">
                        Total Pembayaran
                    </span>
                    <span style="font-size:20px; font-weight:700; color:#B8956F;">
                        {{ $fmtRp($order->grand_total) }}
                    </span>
                </div>
            </td>
        </tr>

        @if ($order->payment_method === 'cash' && $order->cash_received)
            <tr>
                <td style="padding:8px 0 0; color:#64748b; font-size:11px;">Tunai Diterima</td>
                <td style="padding:8px 0 0; text-align:right; color:#111827; font-size:11px;">
                    {{ $fmtRp($order->cash_received) }}
                </td>
            </tr>
            <tr>
                <td style="padding:2px 0; color:#64748b; font-size:11px;">Kembalian</td>
                <td style="padding:2px 0; text-align:right; color:#047857; font-weight:700; font-size:11px;">
                    {{ $fmtRp(max(0, (int) $order->cash_received - (int) $order->grand_total)) }}
                </td>
            </tr>
        @endif
    </table>

    {{-- FOOTER --}}
    <div style="margin-top:28px; padding-top:14px; text-align:center; font-size:10px; color:#94a3b8; border-top:1px solid #E8E0D8;">
        <p style="margin:0;">Terima kasih telah menjadi pelanggan setia Aishi Beauty</p>
        <p style="margin:4px 0 0;">Dokumen ini dicetak secara otomatis dan sah tanpa tanda tangan</p>
    </div>
</div>
