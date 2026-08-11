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
