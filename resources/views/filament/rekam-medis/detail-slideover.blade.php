@php
    use Illuminate\Support\Carbon;

    $tanggal = $rm->appointment?->tanggal_jam
        ? Carbon::parse($rm->appointment->tanggal_jam)
        : Carbon::parse($rm->created_at);

    $jenisMap = [
        'konsultasi' => 'Consultation',
        'treatment'  => 'Treatment',
        'follow_up'  => 'Follow-up',
        'follow-up'  => 'Follow-up',
        'injection'  => 'Injection',
        'procedure'  => 'Procedure',
    ];
    $jenisLabel = $jenisMap[strtolower((string) ($rm->appointment?->jenis))] ?? ucfirst((string) $rm->appointment?->jenis);

    $statusMap = [
        'direncanakan' => ['Direncanakan', '#FEF3C7', '#92400E'],
        'dilakukan'    => ['Dilakukan',    '#D1FAE5', '#065F46'],
        'ditunda'      => ['Ditunda',      '#E5E7EB', '#4B5563'],
        'dibatalkan'   => ['Dibatalkan',   '#FEE2E2', '#991B1B'],
    ];
    $statusCfg = $statusMap[strtolower((string) $rm->status_tindakan)] ?? ['-', '#E5E7EB', '#4B5563'];

    $treatmentName = null;
    if (!empty($rm->tindakan)) {
        $treatmentName = strip_tags($rm->tindakan);
    } elseif ($rm->appointment?->items?->isNotEmpty()) {
        $items = $rm->appointment->items;
        $first = $items->first();
        $treatmentName = match (true) {
            $first->item_type === 'treatment' && $first->treatment       => $first->treatment->nama,
            $first->item_type === 'bundle' && $first->bundle             => 'Bundle: '.$first->bundle->nama,
            $first->item_type === 'consultation' && $first->consultation => $first->consultation->nama,
            default                                                       => null,
        };
        $rest = $items->count() - 1;
        if ($treatmentName && $rest > 0) {
            $treatmentName .= ' + '.$rest.' item';
        }
    }

    $fotoSebelum = $rm->foto_sebelum_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($rm->foto_sebelum_path) : null;
    $fotoSesudah = $rm->foto_sesudah_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($rm->foto_sesudah_path) : null;
@endphp

<div style="padding: 4px 2px; font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
    {{-- HEADER --}}
    <div style="text-align:center; margin-bottom:20px; padding-bottom:16px; border-bottom:2px solid #B8956F;">
        <div style="font-size:11px; font-weight:700; color:#8A8078; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:6px;">
            Rekam Medis
        </div>
        <div style="font-size:20px; font-weight:700; color:#111827; letter-spacing:-0.01em;">
            {{ $rm->no_kunjungan ?? '-' }}
        </div>
        <div style="color:#8A8078; font-size:12px; margin-top:4px;">
            {{ $tanggal->translatedFormat('d F Y, H:i') }}
        </div>
    </div>

    {{-- PASIEN + DOKTER (grid 2 kolom) --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
        <div style="padding:14px; background:#FAF8F5; border:1px solid #EDE8E3; border-radius:10px;">
            <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                Pasien
            </div>
            <div style="font-size:14px; font-weight:700; color:#111827;">
                {{ $rm->pasien->name ?? '-' }}
            </div>
            @if ($rm->pasien?->pasienProfile?->no_rekam_medis)
                <div style="font-size:12px; color:#8A8078; margin-top:2px; font-variant-numeric:tabular-nums;">
                    {{ $rm->pasien->pasienProfile->no_rekam_medis }}
                </div>
            @endif
        </div>

        <div style="padding:14px; background:#FAF8F5; border:1px solid #EDE8E3; border-radius:10px;">
            <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                Dokter
            </div>
            <div style="font-size:14px; font-weight:700; color:#111827;">
                {{ $rm->dokter->name ?? '—' }}
            </div>
            @if ($jenisLabel)
                <div style="font-size:12px; color:#B8956F; font-weight:500; margin-top:2px;">
                    {{ $jenisLabel }}
                </div>
            @endif
        </div>
    </div>

    {{-- STATUS BADGE --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
        <span style="font-size:11px; font-weight:600; color:#8A8078; text-transform:uppercase; letter-spacing:0.08em;">Status:</span>
        <span style="display:inline-block; padding:4px 14px; border-radius:9999px; background:{{ $statusCfg[1] }}; color:{{ $statusCfg[2] }}; font-size:0.75rem; font-weight:600;">
            {{ $statusCfg[0] }}
        </span>
    </div>

    {{-- DETAIL SECTIONS --}}
    <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:20px;">
        @php
            $sections = [
                ['Keluhan', $rm->keluhan],
                ['Diagnosa', $rm->diagnosa],
                ['Treatment', $treatmentName],
                ['Regimen / Produk Rekomendasi', $rm->produk_rekomendasi],
                ['Catatan Lanjutan', $rm->catatan_lanjutan],
            ];
        @endphp

        @foreach ($sections as [$label, $value])
            <div>
                <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                    {{ $label }}
                </div>
                <div style="font-size:14px; color:#111827; line-height:1.55; white-space:pre-wrap;">{{ $value ?: '—' }}</div>
            </div>
        @endforeach
    </div>

    {{-- LAMPIRAN FOTO --}}
    @if ($fotoSebelum || $fotoSesudah)
        <div style="padding-top:16px; border-top:1px solid #EDE8E3;">
            <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:10px; letter-spacing:0.08em;">
                Lampiran Foto
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                @if ($fotoSebelum)
                    <div>
                        <div style="font-size:11px; color:#8A8078; margin-bottom:6px; font-weight:500;">Sebelum</div>
                        <a href="{{ $fotoSebelum }}" target="_blank" rel="noopener" style="display:block;">
                            <img src="{{ $fotoSebelum }}" alt="Foto Sebelum" style="width:100%; height:auto; aspect-ratio:4/3; object-fit:cover; border-radius:10px; border:1px solid #EDE8E3;" />
                        </a>
                    </div>
                @endif
                @if ($fotoSesudah)
                    <div>
                        <div style="font-size:11px; color:#8A8078; margin-bottom:6px; font-weight:500;">Sesudah</div>
                        <a href="{{ $fotoSesudah }}" target="_blank" rel="noopener" style="display:block;">
                            <img src="{{ $fotoSesudah }}" alt="Foto Sesudah" style="width:100%; height:auto; aspect-ratio:4/3; object-fit:cover; border-radius:10px; border:1px solid #EDE8E3;" />
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
