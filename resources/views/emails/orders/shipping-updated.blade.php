@php
    /**
     * @var \App\Models\Order $order
     * @var \App\Models\User $notifiable
     */
    $clinicName     = config('clinic.name', 'Aishi Beauty');
    $clinicTagline  = config('clinic.tagline', 'Beauty Clinic & Skincare');
    $clinicAddress  = config('clinic.address');
    $clinicEmail    = config('clinic.email');
    $clinicWhatsapp = config('clinic.whatsapp');
    $clinicPhone    = config('clinic.phone');

    /* Status copywriting per shipping_status. */
    $statusMap = [
        'dikirim' => [
            'label'   => 'Sedang Dikirim',
            'tone'    => 'info',
            'desc'    => 'Kabar baik! Pesanan Anda sudah dikirim dan sedang menuju alamat tujuan.',
            'subline' => 'Berikut detail pengiriman dan ringkasan pesanan Anda.',
        ],
        'selesai' => [
            'label'   => 'Pesanan Selesai',
            'tone'    => 'success',
            'desc'    => 'Paket Anda telah diterima. Semoga produk perawatan kulit Anda bermanfaat.',
            'subline' => 'Berikut ringkasan akhir pesanan Anda.',
        ],
        'diproses' => [
            'label'   => 'Sedang Diproses',
            'tone'    => 'warning',
            'desc'    => 'Pesanan Anda sedang kami siapkan dan akan segera dikirim.',
            'subline' => 'Berikut detail pesanan Anda.',
        ],
        'dikemas' => [
            'label'   => 'Sedang Dikemas',
            'tone'    => 'warning',
            'desc'    => 'Pesanan Anda sedang kami kemas dengan hati-hati dan akan segera dikirim.',
            'subline' => 'Berikut detail pesanan Anda.',
        ],
        'dibatalkan' => [
            'label'   => 'Pengiriman Dibatalkan',
            'tone'    => 'danger',
            'desc'    => 'Mohon maaf, pengiriman pesanan Anda telah dibatalkan.',
            'subline' => 'Hubungi tim kami melalui kontak di bawah jika Anda butuh bantuan.',
        ],
    ];
    $cfg = $statusMap[$order->shipping_status] ?? [
        'label'   => 'Status Diupdate',
        'tone'    => 'info',
        'desc'    => 'Ada pembaruan status pengiriman pesanan Anda.',
        'subline' => 'Berikut detail terbaru pesanan Anda.',
    ];

    $toneStyles = [
        'info'    => 'background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE;',
        'success' => 'background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0;',
        'warning' => 'background:#FFF8EB; color:#92400E; border:1px solid #FDE68A;',
        'danger'  => 'background:#FEF2F2; color:#9F1239; border:1px solid #FECACA;',
    ];
    $badgeStyle = $toneStyles[$cfg['tone']] ?? $toneStyles['info'];

    $kurir   = strtoupper($order->shipping_method ?? '');
    $resi    = $order->shipping_waybill ?? null;
    $isDikirim = $order->shipping_status === 'dikirim';

    /* Tanggal Dikirim — pakai shipped_at kalau ada, fallback updated_at. */
    $shippedAtRaw = $order->shipped_at ?? $order->updated_at;
    $shippedAt    = $shippedAtRaw ? \Illuminate\Support\Carbon::parse($shippedAtRaw)->translatedFormat('d F Y, H:i') : '-';

    $rupiah = fn ($n) => 'Rp ' . number_format((int) ($n ?? 0), 0, ',', '.');
    $orderUrl = route('dashboard') . '?tab=orders&highlight=' . $order->order_id;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Anda Sedang Dikirim - {{ $order->kode_order }}</title>
    <style>
        body, table, td { font-family: 'Plus Jakarta Sans', 'Segoe UI', Helvetica, Arial, sans-serif; }
    </style>
</head>
<body style="margin:0; padding:0; background:#FAF8F5; color:#1A1A1A;">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAF8F5;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background:#FFFFFF; border:1px solid #E8E0D8; border-radius:18px; overflow:hidden;">

                {{-- ═════ HEADER (BRAND + CLINIC INFO) ═════ --}}
                <tr>
                    <td style="padding:28px 32px 22px; text-align:center; background:linear-gradient(135deg, #FFFFFF 0%, #FAF8F5 100%); border-bottom:1px solid #EDE8E3;">
                        <div style="font-family:'Plus Jakarta Sans', Arial, sans-serif; font-size:24px; font-weight:800; letter-spacing:0.04em; color:#B8956F;">
                            {{ strtoupper($clinicName) }}
                        </div>
                        <div style="font-size:12px; font-weight:500; color:#8C7A63; margin-top:4px; letter-spacing:0.03em;">
                            {{ $clinicTagline }}
                        </div>
                        @if($clinicAddress)
                            <div style="font-size:11px; line-height:1.5; color:#94A3B8; margin-top:8px; max-width:420px; margin-left:auto; margin-right:auto;">
                                {{ $clinicAddress }}
                            </div>
                        @endif
                    </td>
                </tr>

                {{-- ═════ STATUS BADGE & GREETING ═════ --}}
                <tr>
                    <td style="padding:28px 32px 6px;">
                        <div style="text-align:center; margin-bottom:18px;">
                            <span style="display:inline-block; padding:7px 16px; border-radius:9999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.14em; {{ $badgeStyle }}">
                                {{ $cfg['label'] }}
                            </span>
                        </div>

                        <h1 style="margin:0 0 10px; font-size:22px; font-weight:800; color:#1A1A1A; letter-spacing:-0.01em; text-align:center; line-height:1.25;">
                            Halo, {{ $notifiable->name }}
                        </h1>
                        <p style="margin:0 0 6px; font-size:14px; line-height:1.65; color:#475569; text-align:center;">
                            {{ $cfg['desc'] }}
                        </p>
                        <p style="margin:0 0 22px; font-size:13px; line-height:1.6; color:#94A3B8; text-align:center;">
                            {{ $cfg['subline'] }}
                        </p>
                    </td>
                </tr>

                {{-- ═════ INFORMASI PENGIRIMAN ═════ --}}
                <tr>
                    <td style="padding:0 32px 8px;">
                        <p style="margin:0 0 10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.16em; color:#94A3B8;">Informasi Pengiriman</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAF8F5; border:1px solid #EDE8E3; border-radius:12px;">
                            <tr>
                                <td style="padding:14px 16px; border-bottom:1px dashed #EDE8E3;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="font-size:12px; color:#94A3B8; width:40%;">Kode Order</td>
                                            <td style="font-size:13px; color:#1A1A1A; font-weight:700; text-align:right; font-family:'JetBrains Mono', Consolas, monospace; letter-spacing:0.01em;">#{{ $order->kode_order }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px; border-bottom:1px dashed #EDE8E3;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="font-size:12px; color:#94A3B8; width:40%;">Status</td>
                                            <td style="font-size:13px; color:#1A1A1A; font-weight:700; text-align:right;">{{ $cfg['label'] }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @if($kurir)
                            <tr>
                                <td style="padding:14px 16px; border-bottom:1px dashed #EDE8E3;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="font-size:12px; color:#94A3B8; width:40%;">Kurir</td>
                                            <td style="font-size:13px; color:#1A1A1A; font-weight:700; text-align:right;">{{ $kurir }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @endif
                            @if($resi)
                            <tr>
                                <td style="padding:14px 16px; border-bottom:1px dashed #EDE8E3;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="font-size:12px; color:#94A3B8; width:40%;">Nomor Resi</td>
                                            <td style="font-size:13px; color:#B8956F; font-weight:800; text-align:right; font-family:'JetBrains Mono', Consolas, monospace; letter-spacing:0.02em;">{{ $resi }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding:14px 16px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="font-size:12px; color:#94A3B8; width:40%;">{{ $isDikirim ? 'Tanggal Dikirim' : 'Tanggal Update' }}</td>
                                            <td style="font-size:13px; color:#1A1A1A; font-weight:700; text-align:right;">{{ $shippedAt }} WIB</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        @if($resi)
                            <p style="margin:12px 0 0; font-size:12px; line-height:1.55; color:#64748B; text-align:center;">
                                Simpan nomor resi ini untuk melacak paket Anda melalui website kurir.
                            </p>
                        @endif
                    </td>
                </tr>

                {{-- ═════ DETAIL PESANAN ═════ --}}
                @if($order->items && $order->items->count() > 0)
                <tr>
                    <td style="padding:24px 32px 8px;">
                        <p style="margin:0 0 10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.16em; color:#94A3B8;">Detail Pesanan</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FFFFFF; border:1px solid #EDE8E3; border-radius:12px;">
                            @foreach($order->items as $i => $item)
                                <tr>
                                    <td style="padding:14px 16px; {{ $i < $order->items->count() - 1 ? 'border-bottom:1px solid #F5F0EB;' : '' }}">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="vertical-align:top; padding-right:12px;">
                                                    <p style="margin:0 0 3px; font-size:14px; font-weight:700; color:#1A1A1A; line-height:1.35;">{{ $item->description ?? '-' }}</p>
                                                    <p style="margin:0; font-size:12px; color:#94A3B8;">
                                                        {{ $item->qty }} × {{ $rupiah($item->harga_satuan) }}
                                                    </p>
                                                </td>
                                                <td style="vertical-align:top; text-align:right; white-space:nowrap; font-size:13px; font-weight:800; color:#1A1A1A;">
                                                    {{ $rupiah($item->subtotal) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                @endif

                {{-- ═════ RINGKASAN PEMBAYARAN ═════ --}}
                <tr>
                    <td style="padding:20px 32px 8px;">
                        <p style="margin:0 0 10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.16em; color:#94A3B8;">Ringkasan Pembayaran</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:6px 0; font-size:13px; color:#64748B;">Subtotal Produk</td>
                                <td style="padding:6px 0; font-size:13px; color:#1A1A1A; font-weight:700; text-align:right;">{{ $rupiah($order->subtotal) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0; font-size:13px; color:#64748B;">Ongkos Kirim</td>
                                <td style="padding:6px 0; font-size:13px; color:#1A1A1A; font-weight:700; text-align:right;">{{ $rupiah($order->ongkir) }}</td>
                            </tr>
                            @if(($order->diskon_total ?? 0) > 0)
                            <tr>
                                <td style="padding:6px 0; font-size:13px; color:#047857;">Diskon Promo</td>
                                <td style="padding:6px 0; font-size:13px; color:#047857; font-weight:700; text-align:right;">− {{ $rupiah($order->diskon_total) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding:14px 0 0; border-top:1px dashed #EDE8E3; font-size:14px; color:#1A1A1A; font-weight:700;">Total Pembayaran</td>
                                <td style="padding:14px 0 0; border-top:1px dashed #EDE8E3; text-align:right; font-size:18px; font-weight:900; color:#B8956F; letter-spacing:-0.01em;">{{ $rupiah($order->grand_total) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- ═════ ALAMAT PENGIRIMAN ═════ --}}
                @if($order->shipping_address || $notifiable)
                <tr>
                    <td style="padding:24px 32px 8px;">
                        <p style="margin:0 0 10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.16em; color:#94A3B8;">Alamat Pengiriman</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAF8F5; border:1px solid #EDE8E3; border-radius:12px;">
                            <tr>
                                <td style="padding:14px 16px;">
                                    <p style="margin:0 0 4px; font-size:14px; font-weight:800; color:#1A1A1A;">{{ $notifiable->name }}</p>
                                    @if($notifiable->phone)
                                        <p style="margin:0 0 8px; font-size:12px; color:#94A3B8;">{{ $notifiable->phone }}</p>
                                    @endif
                                    <p style="margin:0; font-size:13px; line-height:1.6; color:#475569;">
                                        {{ $order->shipping_address }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                {{-- ═════ CTA ═════ --}}
                <tr>
                    <td style="padding:30px 32px 8px;" align="center">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td align="center" bgcolor="#B8956F" style="border-radius:9999px; background:linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);">
                                    <a href="{{ $orderUrl }}"
                                       style="display:inline-block; padding:14px 34px; font-family:'Plus Jakarta Sans', Arial, sans-serif; font-size:14px; font-weight:800; color:#FFFFFF; text-decoration:none; border-radius:9999px;">
                                        Lihat Detail Pesanan
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- ═════ FOOTER ═════ --}}
                <tr>
                    <td style="padding:28px 32px 30px; text-align:center; border-top:1px solid #EDE8E3;">
                        <p style="margin:0 0 14px; font-size:14px; font-weight:700; color:#1A1A1A;">
                            Terima kasih telah berbelanja di {{ $clinicName }}.
                        </p>

                        <p style="margin:0 0 8px; font-size:12px; font-weight:700; color:#8C7A63; text-transform:uppercase; letter-spacing:0.14em;">
                            Butuh bantuan? Hubungi kami
                        </p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 14px;">
                            @if($clinicWhatsapp)
                            <tr>
                                <td style="padding:3px 0; font-size:12px; color:#64748B; text-align:center;">
                                    WhatsApp: <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $clinicWhatsapp) }}" style="color:#B8956F; font-weight:700; text-decoration:none;">{{ $clinicWhatsapp }}</a>
                                </td>
                            </tr>
                            @endif
                            @if($clinicEmail)
                            <tr>
                                <td style="padding:3px 0; font-size:12px; color:#64748B; text-align:center;">
                                    Email: <a href="mailto:{{ $clinicEmail }}" style="color:#B8956F; font-weight:700; text-decoration:none;">{{ $clinicEmail }}</a>
                                </td>
                            </tr>
                            @endif
                            @if($clinicAddress)
                            <tr>
                                <td style="padding:3px 14px; font-size:12px; color:#64748B; line-height:1.55; text-align:center; max-width:420px;">
                                    Alamat: {{ $clinicAddress }}
                                </td>
                            </tr>
                            @endif
                        </table>

                        <p style="margin:14px 0 6px; font-size:11px; color:#CBD5E1; line-height:1.55; font-style:italic;">
                            Email ini dikirim otomatis, mohon tidak membalas email ini.
                        </p>
                        <p style="margin:0; font-size:11px; color:#CBD5E1; letter-spacing:0.04em;">
                            &copy; {{ date('Y') }} {{ $clinicName }} by Dr. Els. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>

            <p style="margin:16px 0 0; font-size:11px; color:#94A3B8; line-height:1.5; max-width:600px; padding:0 16px;">
                Email ini dikirim ke {{ $notifiable->email }} karena Anda memiliki pesanan aktif di {{ $clinicName }}.
            </p>
        </td>
    </tr>
</table>

</body>
</html>
