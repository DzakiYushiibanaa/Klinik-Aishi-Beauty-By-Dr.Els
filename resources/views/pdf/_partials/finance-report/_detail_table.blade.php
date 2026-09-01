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
