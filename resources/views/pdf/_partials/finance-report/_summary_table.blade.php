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
