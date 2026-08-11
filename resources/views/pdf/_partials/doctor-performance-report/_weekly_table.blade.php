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
