@php
    use Illuminate\Support\Carbon;

    $kodeAppt = 'APT-' . str_pad((string) $appointment->appointment_id, 4, '0', STR_PAD_LEFT);
    $tgl      = $appointment->tanggal_jam ? Carbon::parse($appointment->tanggal_jam) : null;

    $items = $appointment->loadMissing([
        'items.treatment',
        'items.bundle',
        'items.consultation',
    ])->items;

    $fmt = fn ($n) => 'Rp ' . number_format((int) ($n ?? 0), 0, ',', '.');

    $orderItems = $order?->loadMissing(['items.product', 'items.treatment', 'items.bundle'])->items ?? collect();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Layanan Selesai - {{ $kodeAppt }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; color: #1A1A1A; line-height: 1.5; font-size: 13px; background:#F5F0EB; padding: 30px 15px; }
        .card { max-width: 640px; margin: 0 auto; background:#fff; border-radius:12px; overflow:hidden; border:1px solid #E8E0D8; }
        .header { text-align: center; padding: 24px 20px; border-bottom: 2px solid #CFAF53; }
        .logo { font-size: 22px; font-weight: bold; color: #CFAF53; letter-spacing:0.04em; }
        .clinic-info { color: #8C7A63; font-size: 11px; margin-top:4px; }
        .body-wrap { padding: 22px 24px; }
        .thankyou { text-align:center; padding:8px 0 18px; }
        .thankyou h1 { font-size: 18px; color:#111827; font-weight:700; margin-bottom:6px; }
        .thankyou p { color:#475569; font-size:13px; }
        .grid { width: 100%; margin-bottom: 20px; border-collapse:collapse; }
        .grid td { vertical-align: top; width: 50%; padding:0; }
        .section-title { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }
        .info-text { font-size: 12px; margin-bottom: 4px; color:#111827; }
        .info-bold { font-weight: bold; }
        .muted { color:#64748b; }
        table.items { width: 100%; border-collapse: collapse; margin: 12px 0 16px; }
        table.items th { background-color: #F5F0EB; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #E8E0D8; letter-spacing:0.05em; }
        table.items td { padding: 9px 10px; border-bottom: 1px solid #E8E0D8; font-size: 12px; vertical-align:top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .item-name { font-weight:700; color:#111827; }
        .item-meta { color:#94a3b8; font-size:11px; margin-top:2px; }
        .summary-table { width: 100%; margin-top: 6px; border-collapse:collapse; }
        .summary-table td { padding: 5px 0; font-size: 12px; }
        .summary-label { color: #64748b; }
        .summary-value { text-align: right; font-weight: 600; color:#111827; }
        .summary-total td { border-top: 1px dashed #E8E0D8; padding-top: 10px; font-size: 15px; color: #CFAF53; font-weight: bold; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing:0.04em; }
        .status-lunas { background-color: #d1fae5; color: #047857; }
        .status-menunggu { background-color: #fef3c7; color: #b45309; }
        .cta { display:inline-block; background:#CFAF53; color:#fff !important; padding:10px 22px; border-radius:8px; text-decoration:none; font-weight:600; font-size:13px; }
        .cta-wrap { text-align:center; margin: 20px 0 6px; }
        .footer { text-align: center; font-size: 11px; color: #94a3b8; padding: 14px 20px 22px; }
        .chip { display:inline-block; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:600; background:#F5F0EB; color:#8C7A63; margin-left:4px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="logo">AISHI BEAUTY</div>
        <div class="clinic-info">Klinik Aishi Beauty By Dr. Els</div>
        <div class="clinic-info">Jl. Nusantara Raya No.99b, Beji, Kota Depok, Jawa Barat 16421</div>
    </div>

    <div class="body-wrap">
        <div class="thankyou">
            <h1>Terima Kasih, {{ $notifiable->name ?? $appointment->pasien?->name ?? 'Pelanggan' }}</h1>
            <p>Layanan Anda di Aishi Beauty telah selesai. Berikut ringkasan detailnya.</p>
        </div>

        {{-- DETAIL APPOINTMENT + ORDER --}}
        <table class="grid">
            <tr>
                <td>
                    <div class="section-title">Detail Appointment</div>
                    <div class="info-text">Kode: <span class="info-bold">{{ $kodeAppt }}</span></div>
                    <div class="info-text">Tanggal: <span class="info-bold">{{ $tgl ? $tgl->translatedFormat('d F Y, H:i') : '-' }}</span></div>
                    <div class="info-text">Jenis:
                        <span class="info-bold">{{ ucfirst(str_replace('_', ' ', (string) $appointment->jenis)) }}</span>
                    </div>
                    @if ($appointment->dokter)
                        <div class="info-text">Dokter: <span class="info-bold">Dr. {{ $appointment->dokter->name }}</span></div>
                    @endif
                    @if ($appointment->terapis)
                        <div class="info-text">Terapis: <span class="info-bold">{{ $appointment->terapis->name }}</span></div>
                    @endif
                </td>

                <td class="text-right">
                    <div class="section-title">Transaksi</div>
                    @if ($order)
                        <div class="info-text">Kode Order: <span class="info-bold">{{ $order->kode_order }}</span></div>
                        <div class="info-text">Tanggal: <span class="info-bold">{{ $order->created_at?->translatedFormat('d/m/Y') }}</span></div>
                        @if ($order->payment_method)
                            <div class="info-text">Metode:
                                <span class="info-bold">{{ strtoupper($order->payment_method) }}</span>
                            </div>
                        @endif
                        @php
                            $statusText = match (true) {
                                in_array($order->status, ['lunas', 'sudah_dibayar', 'paid'])                   => ['LUNAS', 'status-lunas'],
                                in_array($order->status, ['menunggu_pembayaran', 'pending', 'draft'])          => ['MENUNGGU PEMBAYARAN', 'status-menunggu'],
                                default                                                                        => [strtoupper((string) $order->status), 'status-menunggu'],
                            };
                        @endphp
                        <div style="margin-top:6px;">
                            <span class="status-badge {{ $statusText[1] }}">{{ $statusText[0] }}</span>
                        </div>
                    @else
                        <div class="info-text muted">Tidak ada transaksi terkait.</div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ITEMS TABLE — prefer order items (final pricing), fallback to appointment items --}}
        @php
            $rows = $orderItems->isNotEmpty()
                ? $orderItems->map(function ($oi) {
                    $name = $oi->product?->nama
                        ?? $oi->treatment?->nama
                        ?? $oi->bundle?->nama
                        ?? $oi->description
                        ?? ucfirst((string) $oi->item_type);
                    return [
                        'name'   => $name,
                        'type'   => ucfirst((string) $oi->item_type),
                        'qty'    => (int) ($oi->qty ?? 1),
                        'price'  => (int) ($oi->harga_satuan ?? 0),
                        'subtotal' => (int) (($oi->subtotal ?? ($oi->harga_satuan ?? 0) * ($oi->qty ?? 1))),
                    ];
                })
                : $items->map(function ($it) {
                    $name = $it->treatment?->nama
                        ?? $it->bundle?->nama
                        ?? $it->consultation?->nama
                        ?? ucfirst((string) $it->item_type);
                    $price = match (true) {
                        (bool) $it->treatment    => (int) $it->treatment->harga,
                        (bool) $it->bundle       => (int) ($it->bundle->harga_promo ?: $it->bundle->harga_normal),
                        (bool) $it->consultation => (int) $it->consultation->harga,
                        default                  => 0,
                    };
                    return [
                        'name'   => $name,
                        'type'   => ucfirst((string) $it->item_type),
                        'qty'    => (int) ($it->qty ?? 1),
                        'price'  => $price,
                        'subtotal' => $price * (int) ($it->qty ?? 1),
                    ];
                });
        @endphp

        <table class="items">
            <thead>
                <tr>
                    <th style="width:50%;">Item</th>
                    <th class="text-center" style="width:10%;">Qty</th>
                    <th class="text-right" style="width:20%;">Harga</th>
                    <th class="text-right" style="width:20%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>
                            <div class="item-name">{{ $row['name'] }}</div>
                            <div class="item-meta">{{ $row['type'] }}</div>
                        </td>
                        <td class="text-center">{{ $row['qty'] }}</td>
                        <td class="text-right">{{ $fmt($row['price']) }}</td>
                        <td class="text-right info-bold">{{ $fmt($row['subtotal']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center muted" style="padding:18px;">Tidak ada item</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- SUMMARY --}}
        @if ($order)
            <table class="summary-table">
                <tr>
                    <td class="summary-label" style="width:70%;">Subtotal</td>
                    <td class="summary-value">{{ $fmt($order->subtotal) }}</td>
                </tr>
                @if ((int) ($order->diskon_total ?? 0) > 0)
                    <tr>
                        <td class="summary-label">Diskon</td>
                        <td class="summary-value" style="color:#be123c;">-{{ $fmt($order->diskon_total) }}</td>
                    </tr>
                @endif
                @if ((int) ($order->ongkir ?? 0) > 0)
                    <tr>
                        <td class="summary-label">Ongkos Kirim</td>
                        <td class="summary-value">{{ $fmt($order->ongkir) }}</td>
                    </tr>
                @endif
                <tr class="summary-total">
                    <td>Total Pembayaran</td>
                    <td class="summary-value">{{ $fmt($order->grand_total) }}</td>
                </tr>
            </table>
        @endif

        <div class="cta-wrap">
            <a href="{{ url('/dashboard') }}" class="cta">Lihat Detail di Dashboard</a>
        </div>
    </div>

    <div class="footer">
        <p>Email ini dikirim otomatis. Simpan sebagai bukti layanan.</p>
        <p style="margin-top:3px;">Aishi Beauty Clinic · Depok, Jawa Barat</p>
    </div>
</div>
</body>
</html>
