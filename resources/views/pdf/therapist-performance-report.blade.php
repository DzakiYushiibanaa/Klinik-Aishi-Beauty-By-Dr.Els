<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Terapis — {{ $periodLabel }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #1A1A1A;
            margin: 0;
        }
        .header {
            border-bottom: 3px solid #B8956F;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h1 { margin: 0; font-size: 18px; color: #2C2622; }
        .header .meta { font-size: 9px; color: #555; margin-top: 4px; }

        .section-title {
            background: #2C2622;
            color: #fff;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { padding: 6px 10px; border: 1px solid #E0E0E0; }
        .summary td.label { background: #FAF8F5; font-weight: bold; width: 35%; color: #555; }
        .summary td.value { color: #B8956F; font-weight: bold; }

        .weekly { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .weekly th, .weekly td { padding: 6px 8px; border: 1px solid #E0E0E0; text-align: left; }
        .weekly th { background: #B8956F; color: #fff; font-size: 9px; text-transform: uppercase; }
        .weekly tfoot td { background: #FAF8F5; font-weight: bold; }

        table.detail { width: 100%; border-collapse: collapse; }
        table.detail th {
            background: #2C2622;
            color: #fff;
            padding: 5px 6px;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }
        table.detail td { padding: 4px 6px; border-bottom: 1px solid #EEE; font-size: 8.5px; }
        table.detail tfoot td {
            background: #FAF8F5;
            font-weight: bold;
            border-top: 2px solid #B8956F;
            border-bottom: none;
            padding: 7px 6px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .gold { color: #B8956F; }
        .emerald { color: #047857; font-weight: bold; }
        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #E0E0E0;
            font-size: 8px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Kinerja Terapis</h1>
        <div class="meta">
            Klinik Aishi Beauty by Dr. Els<br>
            Periode: <strong>{{ $periodLabel }}</strong> &nbsp;•&nbsp; Dibuat pada: {{ $now }} WIB
        </div>
    </div>

    <div class="section-title">Ringkasan</div>
    <table class="summary">
        <tr>
            <td class="label">Total Terapis Aktif</td>
            <td class="value">{{ number_format($summary['total_active'], 0, ',', '.') }} orang</td>
            <td class="label">Treatment Ditangani</td>
            <td class="value">{{ number_format($summary['total_treatment'], 0, ',', '.') }} sesi</td>
        </tr>
        <tr>
            <td class="label">Total Pendapatan</td>
            <td class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
            <td class="label">Total Komisi</td>
            <td class="value emerald">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Terapis Terproduktif</td>
            <td colspan="3" class="value">
                {{ $summary['best_therapist']['name'] ?? '—' }}
                @if($summary['best_therapist'])
                    <span style="color:#888; font-weight:normal;">({{ number_format($summary['best_therapist']['count'], 0, ',', '.') }} sesi)</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">Rekap Mingguan</div>
    <table class="weekly">
        <thead>
            <tr>
                <th>Minggu</th>
                <th>Periode</th>
                <th class="text-center">Sesi</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">Komisi</th>
                <th>Terapis Paling Aktif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeks as $w)
                <tr>
                    <td><strong>{{ $w['label'] }}</strong></td>
                    <td>{{ $w['range'] }}</td>
                    <td class="text-center">{{ number_format($w['total_qty'], 0, ',', '.') }}</td>
                    <td class="text-right gold"><strong>Rp {{ number_format($w['total_revenue'], 0, ',', '.') }}</strong></td>
                    <td class="text-right emerald">Rp {{ number_format($w['total_commission'], 0, ',', '.') }}</td>
                    <td>{{ $w['best_therapist'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Bulanan</td>
                <td class="text-center">{{ number_format($weeks->sum('total_qty'), 0, ',', '.') }}</td>
                <td class="text-right gold">Rp {{ number_format($weeks->sum('total_revenue'), 0, ',', '.') }}</td>
                <td class="text-right emerald">Rp {{ number_format($weeks->sum('total_commission'), 0, ',', '.') }}</td>
                <td>—</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Detail Kinerja Terapis</div>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:25px;">No</th>
                <th>Nama Terapis</th>
                <th class="text-center">Treatment</th>
                <th class="text-center">Pasien</th>
                <th>Treatment Terfavorit</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-center">Komisi%</th>
                <th class="text-right">Total Komisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $r['terapis_name'] }}</strong><br>
                        <span style="color:#888;font-size:8px;">{{ $r['kode_terapis'] }}</span>
                    </td>
                    <td class="text-center">{{ number_format($r['total_treatment'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($r['total_pasien'], 0, ',', '.') }}</td>
                    <td>{{ $r['favorite_treatment'] ?? '—' }}</td>
                    <td class="text-right gold">Rp {{ number_format($r['total_revenue'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $r['commission_rate'] }}%</td>
                    <td class="text-right emerald">Rp {{ number_format($r['total_commission'], 0, ',', '.') }}</td>
                    <td>{{ ucfirst($r['status_kerja']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:20px; color:#888;">
                        Belum ada data kinerja terapis pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($rows->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="2" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-center">{{ number_format($tableTotals['total_treatment'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($tableTotals['total_pasien'], 0, ',', '.') }}</td>
                    <td>—</td>
                    <td class="text-right gold">Rp {{ number_format($tableTotals['total_revenue'], 0, ',', '.') }}</td>
                    <td>—</td>
                    <td class="text-right emerald">Rp {{ number_format($tableTotals['total_commission'], 0, ',', '.') }}</td>
                    <td>{{ $tableTotals['therapist_count'] }} terapis</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem Klinik Aishi Beauty.
        Komisi dihitung dari treatment yang sudah selesai dan pembayaran sudah dibayar.
    </div>
</body>
</html>
