<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan — {{ $periodLabel }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1A1A1A;
            margin: 0;
        }
        .header {
            border-bottom: 3px solid #B8956F;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header h1 { margin: 0; font-size: 18px; color: #2C2622; }
        .header .meta { font-size: 8.5px; color: #555; margin-top: 4px; }

        .section-title {
            background: #2C2622;
            color: #fff;
            padding: 6px 10px;
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
        table.detail td { padding: 4px 5px; border-bottom: 1px solid #EEE; font-size: 8px; }
        table.detail tfoot td {
            background: #FAF8F5;
            font-weight: bold;
            border-top: 2px solid #B8956F;
            border-bottom: none;
            padding: 6px 5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .gold { color: #B8956F; }
        .kode { font-family: monospace; font-size: 8px; color: #B8956F; font-weight: bold; }
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
        <h1>Laporan Keuangan</h1>
        <div class="meta">
            Klinik Aishi Beauty by Dr. Els<br>
            Periode: <strong>{{ $periodLabel }}</strong> &nbsp;•&nbsp; Dibuat: {{ $now }} WIB
        </div>
    </div>

    <div class="section-title">Ringkasan</div>
    <table class="summary">
        <tr>
            <td class="label">Total Pendapatan</td>
            <td class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
            <td class="label">Total Transaksi Sudah Dibayar</td>
            <td class="value">{{ number_format($summary['total_transactions'], 0, ',', '.') }} transaksi</td>
        </tr>
        <tr>
            <td class="label">Pendapatan Produk</td>
            <td class="value">Rp {{ number_format($summary['revenue_produk'], 0, ',', '.') }}</td>
            <td class="label">Pendapatan Treatment</td>
            <td class="value">Rp {{ number_format($summary['revenue_treatment'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Pendapatan Konsultasi</td>
            <td class="value">Rp {{ number_format($summary['revenue_konsultasi'], 0, ',', '.') }}</td>
            <td class="label">Pendapatan Membership</td>
            <td class="value">Rp {{ number_format($summary['revenue_membership'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Ongkir</td>
            <td colspan="3" class="value" style="color:#475569;">
                Rp {{ number_format($summary['total_ongkir'], 0, ',', '.') }}
                <span style="color:#888; font-weight:normal;">— hanya pengiriman produk, bukan revenue klinik</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Rekap Mingguan</div>
    <table class="weekly">
        <thead>
            <tr>
                <th>Minggu</th>
                <th>Periode</th>
                <th class="text-right">Total Pendapatan</th>
                <th class="text-right">Produk</th>
                <th class="text-right">Treatment</th>
                <th class="text-right">Konsultasi</th>
                <th class="text-right">Ongkir</th>
                <th class="text-center">Tx</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeks as $w)
                <tr>
                    <td><strong>{{ $w['label'] }}</strong></td>
                    <td>{{ $w['range'] }}</td>
                    <td class="text-right gold"><strong>Rp {{ number_format($w['total_revenue'], 0, ',', '.') }}</strong></td>
                    <td class="text-right">Rp {{ number_format($w['revenue_produk'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($w['revenue_treatment'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($w['revenue_konsultasi'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($w['total_ongkir'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($w['total_transactions'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Bulanan</td>
                <td class="text-right gold">Rp {{ number_format($weeks->sum('total_revenue'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($weeks->sum('revenue_produk'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($weeks->sum('revenue_treatment'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($weeks->sum('revenue_konsultasi'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($weeks->sum('total_ongkir'), 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($weeks->sum('total_transactions'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Detail Transaksi</div>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:25px;">No</th>
                <th>Invoice</th>
                <th>Tanggal Bayar</th>
                <th>Customer</th>
                <th>Jenis</th>
                <th>Metode Bayar</th>
                <th>Status</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Ongkir</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><span class="kode">{{ $r['kode_order'] }}</span></td>
                    <td>{{ $r['paid_at_label'] }}</td>
                    <td>{{ $r['pasien'] }}</td>
                    <td>{{ $r['order_type_label'] }}</td>
                    <td>{{ $r['payment_method_label'] }}</td>
                    <td>{{ $r['status_label'] }}</td>
                    <td class="text-right">Rp {{ number_format($r['subtotal'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($r['ongkir'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ $r['diskon'] > 0 ? '−Rp ' . number_format($r['diskon'], 0, ',', '.') : 'Rp 0' }}</td>
                    <td class="text-right gold"><strong>Rp {{ number_format($r['grand_total'], 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center; padding:20px; color:#888;">
                        Belum ada data keuangan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($rows->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right">TOTAL</td>
                    <td class="text-right">Rp {{ number_format($tableTotals['subtotal'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($tableTotals['ongkir'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ $tableTotals['diskon'] > 0 ? '−Rp ' . number_format($tableTotals['diskon'], 0, ',', '.') : 'Rp 0' }}</td>
                    <td class="text-right gold">Rp {{ number_format($tableTotals['grand_total'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem Klinik Aishi Beauty.
        Pendapatan klinik dihitung sebagai (subtotal − diskon). Ongkir tidak termasuk dalam revenue.
    </div>
</body>
</html>
