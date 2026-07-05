@php
    use Illuminate\Support\Carbon;

    $kodeAppt = 'APT-' . str_pad((string) $appointment->appointment_id, 4, '0', STR_PAD_LEFT);
    $tgl = $appointment->tanggal_jam ? Carbon::parse($appointment->tanggal_jam) : null;

    $jenisLabel = match (strtolower((string) $appointment->jenis)) {
        'konsultasi' => 'Konsultasi',
        'treatment'  => 'Treatment',
        'follow_up'  => 'Follow-up',
        default      => ucfirst((string) $appointment->jenis),
    };

    $statusMap = [
        'diajukan'    => ['Diajukan',   '#FEF3C7', '#92400E'],
        'disetujui'   => ['Disetujui',  '#D1FAE5', '#065F46'],
        'selesai'     => ['Selesai',    '#E0F2FE', '#075985'],
        'batal'       => ['Batal',      '#FEE2E2', '#991B1B'],
        'tidak_hadir' => ['Tidak Hadir','#F3F4F6', '#4B5563'],
    ];
    $statusCfg = $statusMap[strtolower((string) $appointment->status)] ?? [ucfirst((string) $appointment->status ?: '-'), '#E5E7EB', '#4B5563'];

    $items = $appointment->loadMissing([
        'items.treatment',
        
        'items.consultation',
    ])->items;

    $fmt = fn ($n) => 'Rp ' . number_format((int) ($n ?? 0), 0, ',', '.');

    // Resolve price per item for preview total
    $rows = $items->map(function ($it) {
        $name = $it->treatment?->nama
            ?? $it->consultation?->nama
            ?? ucfirst((string) $it->item_type);
        $price = match (true) {
            (bool) $it->treatment    => (int) $it->treatment->harga,
            (bool) $it->consultation => (int) $it->consultation->harga,
            default                  => 0,
        };
        $qty = (int) ($it->qty ?? 1);
        return [
            'name'     => $name,
            'type'     => ucfirst((string) $it->item_type),
            'qty'      => $qty,
            'price'    => $price,
            'subtotal' => $price * $qty,
        ];
    });

    $estTotal = $rows->sum('subtotal');

    // Rekam medis & order relasi (bisa belum ada)
    $rekamMedis = \App\Models\RekamMedis::where('appointment_id', $appointment->appointment_id)->first();
    $order = \App\Models\Order::whereHas('items', fn ($q) => $q->where('appointment_id', $appointment->appointment_id))
        ->orderByDesc('order_id')
        ->first();
@endphp

<div style="padding: 4px 2px; font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
    {{-- HEADER --}}
    <div style="text-align:center; margin-bottom:20px; padding-bottom:16px; border-bottom:2px solid #B8956F;">
        <div style="font-size:11px; font-weight:700; color:#8A8078; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:6px;">
            Appointment
        </div>
        <div style="font-size:20px; font-weight:700; color:#111827; letter-spacing:-0.01em;">
            {{ $kodeAppt }}
        </div>
        <div style="color:#8A8078; font-size:12px; margin-top:4px;">
            {{ $tgl ? $tgl->translatedFormat('d F Y, H:i') : '-' }} WIB
        </div>
        <div style="margin-top:10px;">
            <span style="display:inline-block; padding:4px 14px; border-radius:9999px; background:{{ $statusCfg[1] }}; color:{{ $statusCfg[2] }}; font-size:0.75rem; font-weight:600;">
                {{ $statusCfg[0] }}
            </span>
            <span style="display:inline-block; margin-left:6px; padding:4px 14px; border-radius:9999px; background:#FAF8F5; color:#B8956F; font-size:0.75rem; font-weight:600; border:1px solid #EDE8E3;">
                {{ $jenisLabel }}
            </span>
        </div>
    </div>

    {{-- PASIEN + PETUGAS (grid 2 kolom) --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:18px;">
        <div style="padding:14px; background:#FAF8F5; border:1px solid #EDE8E3; border-radius:10px;">
            <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                Pasien
            </div>
            <div style="font-size:14px; font-weight:700; color:#111827;">
                {{ $appointment->pasien?->name ?? '-' }}
            </div>
            @if ($appointment->pasien?->pasienProfile?->no_rekam_medis)
                <div style="font-size:12px; color:#8A8078; margin-top:2px; font-variant-numeric:tabular-nums;">
                    {{ $appointment->pasien->pasienProfile->no_rekam_medis }}
                </div>
            @endif
            @if ($appointment->pasien?->email)
                <div style="font-size:11px; color:#8A8078; margin-top:4px;">
                    {{ $appointment->pasien->email }}
                </div>
            @endif
        </div>

        <div style="padding:14px; background:#FAF8F5; border:1px solid #EDE8E3; border-radius:10px;">
            <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                {{ $appointment->jenis === 'treatment' ? 'Terapis' : 'Dokter' }}
            </div>
            @if ($appointment->jenis === 'treatment')
                <div style="font-size:14px; font-weight:700; color:#111827;">
                    {{ $appointment->terapis?->name ?? '—' }}
                </div>
            @else
                <div style="font-size:14px; font-weight:700; color:#111827;">
                    {{ $appointment->dokter ? 'Dr. ' . $appointment->dokter->name : '—' }}
                </div>
            @endif
            @if ($appointment->terapis && $appointment->jenis !== 'treatment')
                <div style="font-size:12px; color:#8A8078; margin-top:2px;">
                    Terapis: {{ $appointment->terapis->name }}
                </div>
            @endif
        </div>
    </div>

    {{-- ITEM TABLE --}}
    <div style="margin-bottom:18px;">
        <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:8px; letter-spacing:0.08em;">
            Layanan & Item
        </div>

        @if ($rows->isEmpty())
            <div style="padding:14px; text-align:center; color:#8A8078; font-size:0.8125rem; background:#FAF8F5; border-radius:10px; border:1px dashed #EDE8E3;">
                Tidak ada item layanan.
            </div>
        @else
            <div style="border:1px solid #EDE8E3; border-radius:10px; overflow:hidden;">
                @foreach ($rows as $i => $row)
                    <div style="padding:12px 14px; display:flex; align-items:center; justify-content:space-between; gap:12px; {{ $i > 0 ? 'border-top:1px solid #EDE8E3;' : '' }} background:{{ $i % 2 === 0 ? '#fff' : '#FAF8F5' }};">
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; color:#111827; font-size:13.5px;">
                                {{ $row['name'] }}
                            </div>
                            <div style="color:#8A8078; font-size:11px; margin-top:2px;">
                                {{ $row['type'] }} · Qty {{ $row['qty'] }}
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-weight:700; color:#111827; font-size:13.5px;">
                                {{ $fmt($row['subtotal']) }}
                            </div>
                            @if ($row['qty'] > 1)
                                <div style="color:#8A8078; font-size:11px; margin-top:2px;">
                                    {{ $fmt($row['price']) }} / item
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 14px; background:#FEF7E6; border:1px solid #F3E4C4; border-radius:10px; margin-top:10px;">
                <span style="color:#7A5A3A; font-size:0.875rem; font-weight:500;">Estimasi Total</span>
                <span style="color:#B8956F; font-size:1.125rem; font-weight:700; letter-spacing:-0.01em;">
                    {{ $fmt($estTotal) }}
                </span>
            </div>
        @endif
    </div>

    {{-- CATATAN --}}
    @if ($appointment->catatan_pasien || $appointment->catatan_klinik)
        <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:18px;">
            @if ($appointment->catatan_pasien)
                <div>
                    <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                        Catatan Pasien
                    </div>
                    <div style="font-size:13px; color:#111827; line-height:1.55; white-space:pre-wrap; padding:10px 12px; background:#FAF8F5; border-radius:10px; border:1px solid #EDE8E3;">{{ $appointment->catatan_pasien }}</div>
                </div>
            @endif

            @if ($appointment->catatan_klinik)
                <div>
                    <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                        Catatan Klinik
                    </div>
                    <div style="font-size:13px; color:#111827; line-height:1.55; white-space:pre-wrap; padding:10px 12px; background:#FAF8F5; border-radius:10px; border:1px solid #EDE8E3;">{{ $appointment->catatan_klinik }}</div>
                </div>
            @endif
        </div>
    @endif

    {{-- RELASI: REKAM MEDIS & ORDER --}}
    @if ($rekamMedis || $order)
        <div style="padding-top:16px; border-top:1px solid #EDE8E3;">
            <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:10px; letter-spacing:0.08em;">
                Dokumen Terkait
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
                @if ($rekamMedis)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 14px; background:#fff; border:1px solid #EDE8E3; border-radius:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:8px; background:#FEF7E6; display:flex; align-items:center; justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#B8956F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div>
                                <div style="font-weight:600; color:#111827; font-size:13px;">Rekam Medis</div>
                                <div style="color:#8A8078; font-size:11px; font-variant-numeric:tabular-nums;">{{ $rekamMedis->no_kunjungan }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($order)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 14px; background:#fff; border:1px solid #EDE8E3; border-radius:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:8px; background:#FEF7E6; display:flex; align-items:center; justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#B8956F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                            </div>
                            <div>
                                <div style="font-weight:600; color:#111827; font-size:13px;">Transaksi</div>
                                <div style="color:#8A8078; font-size:11px; font-variant-numeric:tabular-nums;">{{ $order->kode_order }}</div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700; color:#111827; font-size:13px;">{{ $fmt($order->grand_total) }}</div>
                            @php
                                $orderStatusCfg = match (strtolower((string) $order->status)) {
                                    'sudah_dibayar'                                  => ['Sudah Dibayar', '#D1FAE5', '#065F46'],
                                    'menunggu_pembayaran', 'pending', 'draft'        => ['Menunggu', '#FEF3C7', '#92400E'],
                                    'batal', 'cancelled'                             => ['Batal', '#FEE2E2', '#991B1B'],
                                    default                                           => [ucfirst((string) $order->status), '#E5E7EB', '#4B5563'],
                                };
                            @endphp
                            <span style="display:inline-block; margin-top:2px; padding:2px 8px; border-radius:9999px; background:{{ $orderStatusCfg[1] }}; color:{{ $orderStatusCfg[2] }}; font-size:10px; font-weight:600;">
                                {{ $orderStatusCfg[0] }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
