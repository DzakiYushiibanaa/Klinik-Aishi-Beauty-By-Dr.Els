<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Dokter — {{ $periodLabel }}</title>
    <style>
        @page { margin: 14mm 10mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            color: #1A1A1A;
            margin: 0;
        }
        .header {
            border-bottom: 3px solid #B8956F;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header h1 { margin: 0; font-size: 17px; color: #2C2622; }
        .header .meta { font-size: 8.5px; color: #555; margin-top: 4px; }

        .section-title {
            background: #2C2622;
            color: #fff;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .summary { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary td { padding: 5px 8px; border: 1px solid #E0E0E0; font-size: 9px; }
        .summary td.label { background: #FAF8F5; font-weight: bold; width: 22%; color: #555; }
        .summary td.value { color: #B8956F; font-weight: bold; }

        .weekly { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .weekly th, .weekly td { padding: 5px 6px; border: 1px solid #E0E0E0; text-align: left; font-size: 8.5px; }
        .weekly th { background: #B8956F; color: #fff; text-transform: uppercase; font-size: 8px; }
        .weekly tfoot td { background: #FAF8F5; font-weight: bold; }

        table.detail { width: 100%; border-collapse: collapse; }
        table.detail th {
            background: #2C2622;
            color: #fff;
            padding: 4px 5px;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: left;
        }
        table.detail td { padding: 3px 5px; border-bottom: 1px solid #EEE; font-size: 7.5px; vertical-align: top; }
        table.detail tfoot td {
            background: #FAF8F5;
            font-weight: bold;
            border-top: 2px solid #B8956F;
            border-bottom: none;
            padding: 5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .gold { color: #B8956F; }
        .emerald { color: #047857; font-weight: bold; }
        .footer {
            margin-top: 15px;
            padding-top: 6px;
            border-top: 1px solid #E0E0E0;
            font-size: 7.5px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Kinerja Dokter</h1>
        <div class="meta">
            Klinik Aishi Beauty by Dr. Els<br>
            Periode: <strong>{{ $periodLabel }}</strong> &nbsp;•&nbsp; Dibuat: {{ $now }} WIB
        </div>
    </div>

    <div class="section-title">Ringkasan</div>
    <table class="summary">
        <tr>
            <td class="label">Total Dokter Aktif</td>
            <td class="value">{{ number_format($summary['total_active'], 0, ',', '.') }} orang</td>
            <td class="label">Total Konsultasi</td>
            <td class="value">{{ number_format($summary['total_konsultasi'], 0, ',', '.') }} sesi</td>
        </tr>
        <tr>
            <td class="label">Treatment Ditangani</td>
            <td class="value">{{ number_format($summary['total_treatment'], 0, ',', '.') }} sesi</td>
            <td class="label">Total Pendapatan</td>
            <td class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Komisi</td>
            <td class="value emerald">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</td>
            <td class="label">Dokter Terproduktif</td>
            <td class="value">
                {{ $summary['best_doctor']['name'] ?? '—' }}
                @if($summary['best_doctor'])
                    <span style="color:#888; font-weight:normal;">({{ $summary['best_doctor']['count'] }} layanan)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Rate Komisi</td>
            <td colspan="3" class="value" style="color:#475569;">Konsultasi 15% • Treatment 10%</td>
        </tr>
    </table>

    <div class="section-title">Rekap Mingguan</div>
    <table class="weekly">
        <thead>
            <tr>
                <th>Minggu</th>
                <th>Periode</th>
                <th class="text-center">Konsul Awal</th>
                <th class="text-center">Konsul Lanjutan</th>
                <th class="text-center">Treatment</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">Komisi</th>
                <th>Dokter Paling Aktif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeks as $w)
                <tr>
                    <td><strong>{{ $w['label'] }}</strong></td>
                    <td>{{ $w['range'] }}</td>
                    <td class="text-center">{{ number_format($w['konsultasi_awal'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($w['konsultasi_lanjutan'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($w['total_treatment'], 0, ',', '.') }}</td>
                    <td class="text-right gold"><strong>Rp {{ number_format($w['total_revenue'], 0, ',', '.') }}</strong></td>
                    <td class="text-right emerald">Rp {{ number_format($w['total_commission'], 0, ',', '.') }}</td>
                    <td>{{ $w['best_doctor'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Bulanan</td>
                <td class="text-center">{{ number_format($weeks->sum('konsultasi_awal'), 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($weeks->sum('konsultasi_lanjutan'), 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($weeks->sum('total_treatment'), 0, ',', '.') }}</td>
                <td class="text-right gold">Rp {{ number_format($weeks->sum('total_revenue'), 0, ',', '.') }}</td>
                <td class="text-right emerald">Rp {{ number_format($weeks->sum('total_commission'), 0, ',', '.') }}</td>
                <td>—</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Detail Kinerja Dokter</div>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:20px;">No</th>
                <th>Nama Dokter</th>
                <th class="text-center">Konsul Awal</th>
                <th class="text-center">Konsul Lanjut</th>
                <th class="text-center">Total Konsul</th>
                <th>Treatment Ditangani</th>
                <th class="text-center">Total Tx</th>
                <th class="text-center">Pasien</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">Komisi Konsul (15%)</th>
                <th class="text-right">Komisi Tx (10%)</th>
                <th class="text-right">Total Komisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $r['dokter_name'] }}</strong><br>
                        <span style="color:#888; font-size:7px;">{{ $r['kode_dokter'] }}</span>
                    </td>
                    <td class="text-center">{{ number_format($r['konsultasi_awal'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($r['konsultasi_lanjutan'], 0, ',', '.') }}</td>
                    <td class="text-center"><strong>{{ number_format($r['total_konsultasi'], 0, ',', '.') }}</strong></td>
                    <td>
                        @if(!empty($r['treatment_list']))
                            @foreach(array_slice($r['treatment_list'], 0, 3) as $t)
                                <span style="display:inline-block; background:#F2EFE9; color:#8C7A63; padding:1px 5px; border-radius:6px; font-size:7px; margin:1px;">{{ \Illuminate\Support\Str::limit($t['name'], 18) }} {{ $t['qty'] }}x</span>
                            @endforeach
                            @if(count($r['treatment_list']) > 3)
                                <span style="display:inline-block; background:#F3F4F6; color:#6B7280; padding:1px 5px; border-radius:6px; font-size:7px;">+{{ count($r['treatment_list']) - 3 }} lainnya</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($r['total_treatment'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($r['total_pasien'], 0, ',', '.') }}</td>
                    <td class="text-right gold"><strong>Rp {{ number_format($r['total_revenue'], 0, ',', '.') }}</strong></td>
                    <td class="text-right emerald">Rp {{ number_format($r['komisi_konsultasi'], 0, ',', '.') }}</td>
                    <td class="text-right emerald">Rp {{ number_format($r['komisi_treatment'], 0, ',', '.') }}</td>
                    <td class="text-right emerald"><strong>Rp {{ number_format($r['total_komisi'], 0, ',', '.') }}</strong></td>
                    <td>{{ ucfirst($r['status_praktik']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center; padding:20px; color:#888;">
                        Belum ada data kinerja dokter pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($rows->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td class="text-center">{{ number_format($tableTotals['konsultasi_awal'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($tableTotals['konsultasi_lanjutan'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($tableTotals['total_konsultasi'], 0, ',', '.') }}</td>
                    <td>—</td>
                    <td class="text-center">{{ number_format($tableTotals['total_treatment'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($tableTotals['total_pasien'], 0, ',', '.') }}</td>
                    <td class="text-right gold">Rp {{ number_format($tableTotals['total_revenue'], 0, ',', '.') }}</td>
                    <td class="text-right emerald">Rp {{ number_format($tableTotals['komisi_konsultasi'], 0, ',', '.') }}</td>
                    <td class="text-right emerald">Rp {{ number_format($tableTotals['komisi_treatment'], 0, ',', '.') }}</td>
                    <td class="text-right emerald">Rp {{ number_format($tableTotals['total_komisi'], 0, ',', '.') }}</td>
                    <td>{{ $tableTotals['doctor_count'] }} dokter</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem Klinik Aishi Beauty.
        Komisi konsultasi 15% dan komisi treatment 10% dihitung dari layanan selesai dengan pembayaran sudah dibayar.
    </div>
</body>
</html>
