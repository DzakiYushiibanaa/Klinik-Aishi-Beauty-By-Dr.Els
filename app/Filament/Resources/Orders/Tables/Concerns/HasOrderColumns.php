<?php

namespace App\Filament\Resources\Orders\Tables\Concerns;

use App\Models\Order;
use App\Support\PaymentMethodFormatter;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasOrderColumns
{
    private static function columnPesananWaktu(): TextColumn
    {
        return TextColumn::make('pesanan_waktu')
            ->label('Pesanan & Waktu')
            ->html()
            ->state(function (Order $record) {
                $kode = e($record->kode_order ?? '-');
                $dt = $record->created_at ? Carbon::parse($record->created_at) : null;
                $waktu = $dt ? $dt->format('d M Y, H:i') : '-';
                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="color:#B8956F; font-weight:500;">'.$kode.'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.e($waktu).'</span>
                    </div>
                ';
            })
            ->searchable(query: fn (Builder $q, string $s) => $q->where('kode_order', 'like', "%{$s}%"))
            ->sortable(query: fn (Builder $q, string $d) => $q->orderBy('created_at', $d));
    }

    private static function columnPelanggan(): TextColumn
    {
        return TextColumn::make('pelanggan')
            ->label('Pelanggan')
            ->html()
            ->state(function (Order $record) {
                $nama = e($record->pasien?->name ?? '-');
                $rm = e($record->pasien?->pasienProfile?->no_rekam_medis ?? '-');
                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:700; color:#111827;">'.$nama.'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$rm.'</span>
                    </div>
                ';
            })
            ->searchable(query: function (Builder $query, string $search) {
                return $query->whereHas('pasien', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('pasien.pasienProfile', fn ($q) => $q->where('no_rekam_medis', 'like', "%{$search}%"));
            });
    }

    private static function columnDetailPengiriman(): TextColumn
    {
        return TextColumn::make('detail_pengiriman')
            ->label('Detail & Pengiriman')
            ->html()
            ->state(function (Order $record) {
                $items = $record->items;
                $detail = $items->isEmpty() ? '-' : self::firstItemSummary($items);

                $kurir = $record->shipping_method
                    ? ucwords(str_replace('_', ' ', $record->shipping_method))
                    : '-';
                $resi = $record->shipping_waybill ?: '-';

                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="color:#111827;">'.e($detail).'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.e($kurir).' · Resi: '.e($resi).'</span>
                    </div>
                ';
            });
    }

    private static function columnAlamat(): TextColumn
    {
        return TextColumn::make('shipping_address')
            ->label('Alamat')
            ->html()
            ->state(fn (Order $record) => $record->shipping_address
                ? '<span style="color:#111827; font-size:0.875rem;">'.e(\Illuminate\Support\Str::limit($record->shipping_address, 60)).'</span>'
                : '<span style="color:#8A8078; font-size:0.8125rem;">—</span>'
            )
            ->tooltip(fn (Order $record) => $record->shipping_address);
    }

    private static function columnTagihan(): TextColumn
    {
        return TextColumn::make('tagihan')
            ->label('Tagihan')
            ->html()
            ->state(function (Order $record) {
                $total = 'Rp ' . number_format((int) ($record->grand_total ?? 0), 0, ',', '.');

                $isUnpaid = in_array(strtolower((string) ($record->status ?? '')), ['menunggu_pembayaran', 'pending', 'draft'], true);
                $emptyLabel = $isUnpaid ? 'Menunggu Pembayaran' : 'Belum Dipilih';

                $pay = \App\Support\PaymentMethodFormatter::format($record, [
                    'empty_label' => $emptyLabel,
                ]);

                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:700; color:#111827;">'.e($total).'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px; font-weight:500;">'.e($pay).'</span>
                    </div>
                ';
            })
            ->sortable(query: fn (Builder $q, string $d) => $q->orderBy('grand_total', $d));
    }

    private static function columnStatus(): TextColumn
    {
        return TextColumn::make('status')
            ->label('Status')
            ->html()
            ->state(function (Order $record) {
                return self::renderStatusBadge($record->status)
                    . self::renderShippingBadge($record->shipping_status);
            });
    }

}
