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

    @include('pdf._partials.doctor-performance-report._summary_table')
    @include('pdf._partials.doctor-performance-report._weekly_table')
    @include('pdf._partials.doctor-performance-report._detail_table')

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem Klinik Aishi Beauty.
        Komisi konsultasi 15% dan komisi treatment 10% dihitung dari layanan selesai dengan pembayaran sudah dibayar.
    </div>
</body>
</html>
