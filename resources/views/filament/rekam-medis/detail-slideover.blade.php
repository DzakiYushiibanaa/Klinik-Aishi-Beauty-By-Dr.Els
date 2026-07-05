@php
    use Illuminate\Support\Carbon;

    $tanggal = $rm->appointment?->tanggal_jam
        ? Carbon::parse($rm->appointment->tanggal_jam)
        : Carbon::parse($rm->created_at);

    /* ── Resolve jenis layanan dengan detail spesifik ───────────────── */
    $jenisRaw = strtolower((string) ($rm->appointment?->jenis ?? ''));

    if ($jenisRaw === 'konsultasi') {
        // Konsultasi: ambil nama spesifik dari Consultation
        // (mis. "Konsultasi Awal" / "Konsultasi Lanjutan").
        $consultItem = $rm->appointment?->items
            ?->first(fn ($i) => strtolower((string) $i->item_type) === 'consultation');
        $jenisLabel = $consultItem?->consultation?->nama ?: 'Konsultasi';
    } else {
        $jenisMap = [
            'treatment'  => 'Treatment',
            'follow_up'  => 'Follow-up',
            'follow-up'  => 'Follow-up',
            'injection'  => 'Injection',
            'procedure'  => 'Procedure',
        ];
        $jenisLabel = $jenisMap[$jenisRaw] ?? ($jenisRaw !== '' ? ucfirst($jenisRaw) : null);
    }

    $treatmentName = null;
    $dokterHandlerDetails = collect();
    $terapisHandlers = collect();
    $terapisHandlerDetails = collect();
    if (!empty($rm->tindakan)) {
        $treatmentName = strip_tags($rm->tindakan);
    }

    if ($rm->appointment?->items?->isNotEmpty()) {
        // Skip item type "consultation" — konsultasi bukan treatment medis.
        $items = $rm->appointment->items->filter(
            fn ($i) => in_array(strtolower((string) $i->item_type), ['treatment'], true)
        )->values();

        $dokterHandlerDetails = $items
            ->filter(fn ($i) => strtolower((string) $i->handled_by_type) === 'dokter' && $i->handler)
            ->map(function ($i) {
                $serviceName = strtolower((string) $i->item_type) === 'treatment'
                    ? ($i->treatment?->nama ?? 'Treatment')
                    : 'Treatment';

                return [
                    'name' => $i->handler->name,
                    'service' => $serviceName,
                ];
            })
            ->values();

        $terapisHandlerDetails = $items
            ->filter(fn ($i) => strtolower((string) $i->handled_by_type) === 'terapis' && $i->handler)
            ->map(function ($i) {
                $serviceName = strtolower((string) $i->item_type) === 'treatment'
                    ? ($i->treatment?->nama ?? 'Treatment')
                    : 'Treatment';

                return [
                    'name' => $i->handler->name,
                    'service' => $serviceName,
                ];
            })
            ->values();

        $terapisHandlers = $terapisHandlerDetails
            ->pluck('name')
            ->filter()
            ->unique()
            ->values();

        if ($items->isNotEmpty()) {
            $first = $items->first();
            if (!$treatmentName) {
                $treatmentName = match (true) {
                    strtolower((string) $first->item_type) === 'treatment' && $first->treatment => $first->treatment->nama,

                    default => null,
                };

                $rest = $items->count() - 1;
                if ($treatmentName && $rest > 0) {
                    $treatmentName .= ' + '.$rest.' item';
                }
            }
        }
    }

    if ($terapisHandlers->isEmpty() && $rm->terapis) {
        $terapisHandlers = collect([$rm->terapis->name]);
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

    {{-- PASIEN + DOKTER + TERAPIS --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:20px;">
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
            @if ($dokterHandlerDetails->isNotEmpty())
                <div style="font-size:12px; color:#B8956F; font-weight:500; margin-top:4px; line-height:1.45;">
                    @foreach ($dokterHandlerDetails as $detail)
                        <div>{{ $detail['service'] }}</div>
                    @endforeach
                </div>
            @elseif ($jenisLabel)
                <div style="font-size:12px; color:#B8956F; font-weight:500; margin-top:2px;">
                    {{ $jenisLabel }}
                </div>
            @endif
        </div>

        <div style="padding:14px; background:#FAF8F5; border:1px solid #EDE8E3; border-radius:10px;">
            <div style="font-size:10px; font-weight:700; color:#8A8078; text-transform:uppercase; margin-bottom:6px; letter-spacing:0.08em;">
                Terapis
            </div>
            @if ($terapisHandlers->isNotEmpty())
                <div style="display:flex; flex-direction:column; gap:5px;">
                    @foreach ($terapisHandlers as $name)
                        <div style="font-size:14px; font-weight:700; color:#111827;">
                            {{ $name }}
                        </div>
                    @endforeach
                </div>
                @if ($terapisHandlerDetails->isNotEmpty())
                    <div style="font-size:12px; color:#B8956F; font-weight:500; margin-top:4px; line-height:1.45;">
                        @foreach ($terapisHandlerDetails as $detail)
                            <div>{{ $detail['service'] }}</div>
                        @endforeach
                    </div>
                @else
                    <div style="font-size:12px; color:#B8956F; font-weight:500; margin-top:2px;">
                        Menangani treatment
                    </div>
                @endif
            @else
                <div style="font-size:14px; font-weight:700; color:#111827;">—</div>
            @endif
        </div>
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
