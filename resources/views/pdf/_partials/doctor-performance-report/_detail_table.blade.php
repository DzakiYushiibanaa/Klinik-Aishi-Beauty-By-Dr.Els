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
