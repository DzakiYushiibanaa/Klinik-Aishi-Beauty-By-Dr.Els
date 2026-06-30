<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Nota Pesanan - {{ $order->kode_order }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: #1A1A1A; line-height: 1.5; font-size: 12px; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #CFAF53; padding-bottom: 15px; }
        .logo { font-size: 22px; font-weight: bold; color: #CFAF53; margin-bottom: 5px; }
        .clinic-info { color: #8C7A63; font-size: 10px; }
        .grid { width: 100%; margin-bottom: 30px; }
        .grid td { vertical-align: top; width: 50%; }
        .section-title { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }
        .info-text { font-size: 11px; margin-bottom: 4px; }
        .info-bold { font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { background-color: #F5F0EB; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #E8E0D8; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #E8E0D8; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-table { width: 100%; margin-top: 10px; }
        .summary-table td { padding: 4px 0; font-size: 11px; }
        .summary-label { color: #64748b; }
        .summary-value { text-align: right; font-weight: bold; }
        .summary-total td { border-top: 1px dashed #E8E0D8; padding-top: 10px; font-size: 14px; color: #CFAF53; font-weight: bold; }
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .status-paid { background-color: #d1fae5; color: #047857; }
        .status-menunggu { background-color: #fef3c7; color: #b45309; }
        .status-batal { background-color: #ffe4e6; color: #be123c; }
        .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #E8E0D8; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">AISHI BEAUTY</div>
        <div class="clinic-info">Klinik Aishi Beauty By Dr. Els</div>
        <div class="clinic-info">Jl. Nusantara Raya No.99b, Beji, Kec. Beji, Kota Depok, Jawa Barat 16421</div>
    </div>

    @php
        $statusClass = 'status-paid';
        $statusText = 'SUDAH DIBAYAR';
        if(in_array($order->status ?? '', ['batal','dibatalkan']) || ($order->shipping_status ?? '') === 'dibatalkan'){
            $statusClass = 'status-batal';
            $statusText = 'DIBATALKAN';
        } elseif(in_array($order->status ?? '', ['menunggu_pembayaran', 'draft'])){
            $statusClass = 'status-menunggu';
            $statusText = 'MENUNGGU PEMBAYARAN';
        }

        $rawChannel = $order->payment_channel ?? $order->payment_method ?? '';
        $channelLabels = [
            'qris' => 'QRIS',
            'bca' => 'Bank BCA (VA)',
            'bni' => 'Bank BNI (VA)',
            'bri' => 'Bank BRI (VA)',
            'mandiri' => 'Mandiri Bill',
            'mandiri_bill' => 'Mandiri Bill',
            'permata' => 'Bank Permata (VA)',
            'cimb' => 'Bank CIMB (VA)',
            'bsi' => 'Bank BSI (VA)',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'dana' => 'DANA',
            'alfamart' => 'Alfamart',
            'indomaret' => 'Indomaret',
            'bank_transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'akulaku' => 'Akulaku',
            'kredivo' => 'Kredivo',
            'web_redirect' => 'Midtrans',
            'midtrans_snap' => 'Midtrans',
        ];
        $paymentLabel = $rawChannel ? ($channelLabels[strtolower($rawChannel)] ?? strtoupper($rawChannel)) : 'Transfer/Qris';
    @endphp

    @php
        /* ── Resolve nomor telepon (filter blank / "-") ─────────────── */
        $phoneCandidates = [
            $order->user?->phone,
            $order->user?->pasienProfile?->no_telepon,
        ];
        $phone = collect($phoneCandidates)
            ->map(fn ($v) => trim((string) $v))
            ->first(fn ($v) => $v !== '' && $v !== '-');

        /* ── Resolve alamat lengkap (prefer shipping_address, fallback profil) ── */
        $needsShippingAddress = $order->items->isNotEmpty()
            && $order->items->every(fn ($item) => ! empty($item->product_id));

        $profile = $order->user?->pasienProfile;
        $fullAddress = trim((string) ($order->shipping_address ?? ''));
        if ($fullAddress === '' && $profile) {
            $parts = array_filter([
                $profile->alamat ?? null,
                $profile->district_name ?? null,
                $profile->kota ?? null,
                $profile->provinsi ?? null,
                $profile->kode_pos ?? null,
            ], fn ($p) => trim((string) $p) !== '');
            $fullAddress = trim(implode(', ', $parts));
        }
    @endphp

    <table class="grid">
        <tr>
            <td>
                <div class="section-title">Detail Pelanggan</div>
                <div class="info-text info-bold">{{ $order->user->name ?? '-' }}</div>
                @if($order->user?->email)
                    <div class="info-text">{{ $order->user->email }}</div>
                @endif
                <div class="info-text">
                    {{ $phone ?: 'Nomor HP belum diisi' }}
                </div>
                @if($needsShippingAddress && $fullAddress !== '')
                    <div class="info-text" style="margin-top:6px; line-height:1.5;">
                        {{ $fullAddress }}
                    </div>
                @endif
            </td>
            <td class="text-right">
                <div class="section-title">Detail Transaksi</div>
                <div class="info-text">Kode: <span class="info-bold">{{ $order->kode_order }}</span></div>
                <div class="info-text">Tanggal: <span class="info-bold">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}</span></div>
                <div class="info-text">Metode: <span class="info-bold">{{ $paymentLabel }}</span></div>
                <div><span class="status-badge {{ $statusClass }}">{{ $statusText }}</span></div>
                @if($order->shipping_method)
                    <div class="info-text" style="margin-top:5px;">Kurir: <span class="info-bold">{{ strtoupper($order->shipping_method) }}</span></div>
                    @if($order->shipping_waybill)
                        <div class="info-text">Resi: <span class="info-bold">{{ $order->shipping_waybill }}</span></div>
                    @endif
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:50%;">Produk / Treatment</th>
                <th class="text-center" style="width:10%;">Qty</th>
                <th class="text-right" style="width:20%;">Harga</th>
                <th class="text-right" style="width:20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $item)
            <tr>
                <td>
                    <span class="info-bold">{{ $item->product->nama ?? $item->treatment->nama ?? $item->bundle->nama ?? $item->description ?? 'Item' }}</span>
                </td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">Rp {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right info-bold">Rp {{ number_format(($item->harga_satuan ?? 0) * ($item->qty ?? 1), 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding:20px; color:#94a3b8;">Tidak ada item</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-label" style="width:70%;">Subtotal</td>
            <td class="summary-value">Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</td>
        </tr>
        @if(($order->diskon_total ?? 0) > 0)
        <tr>
            <td class="summary-label">Diskon</td>
            <td class="summary-value" style="color: #be123c;">-Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if(($order->ongkir ?? 0) > 0)
        <tr>
            <td class="summary-label">Ongkos Kirim</td>
            <td class="summary-value">Rp {{ number_format($order->ongkir, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="summary-total">
            <td>Total Pembayaran</td>
            <td class="summary-value">Rp {{ number_format($order->grand_total ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Terima kasih telah menjadi pelanggan setia Aishi Beauty</p>
        <p>Dokumen ini dicetak secara otomatis dan sah tanpa tanda tangan</p>
    </div>
</body>
</html>
