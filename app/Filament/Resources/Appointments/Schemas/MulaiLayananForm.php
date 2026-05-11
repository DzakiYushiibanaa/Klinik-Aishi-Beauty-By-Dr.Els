<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\Bundle;
use App\Models\Consultation;
use App\Models\Product;
use App\Models\Treatment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

/**
 * Form schema for the "Mulai Layanan" modal action on the Appointments table.
 *
 * Produces everything needed by StartServiceFinalizer::complete():
 *   consultation_id (for konsultasi), treatment_items[],
 *   keluhan, diagnosa, produk_rekomendasi_items[], catatan_lanjutan,
 *   foto_sebelum_path, foto_sesudah_path, payment_method, payment_status.
 */
class MulaiLayananForm
{
    /**
     * Returns the schema components for the Filament Action form.
     *
     * @return array<int,\Filament\Schemas\Components\Component>
     */
    public static function schema(Appointment $appointment): array
    {
        return [
            // ── 1. INFORMASI PASIEN & LAYANAN (read-only summary) ──
            Section::make('Informasi Pasien & Layanan')
                ->compact()
                ->columns(2)
                ->schema([
                    Placeholder::make('pasien_label')
                        ->label('Pasien')
                        ->content(function () use ($appointment): HtmlString {
                            $nama = e($appointment->pasien?->name ?? '-');
                            $rm = e($appointment->pasien?->pasienProfile?->no_rekam_medis ?? '');
                            $meta = $rm ? ' <span style="color:#8A8078;">(' . $rm . ')</span>' : '';
                            return new HtmlString('<span style="font-weight:600; color:#111827;">' . $nama . '</span>' . $meta);
                        }),

                    Placeholder::make('jenis_label')
                        ->label('Jenis Layanan')
                        ->content(fn (): HtmlString => new HtmlString(
                            '<span style="font-weight:600; color:#B8956F; text-transform:capitalize;">'
                            . e(str_replace('_', ' ', (string) $appointment->jenis))
                            . '</span>'
                        )),
                ]),

            // ── 2. DETAIL LAYANAN ──
            Section::make('Detail Layanan')
                ->columns(2)
                ->schema([
                    // Pilih Konsultasi (hanya kalau jenis konsultasi)
                    Select::make('consultation_id')
                        ->label('Pilih Konsultasi')
                        ->options(fn () => Consultation::query()
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->consultation_id => $c->nama . ' — Rp ' . number_format((int) $c->harga, 0, ',', '.'),
                            ])
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(function () use ($appointment) {
                            return $appointment->items()
                                ->where('item_type', 'consultation')
                                ->value('consultation_id');
                        })
                        ->visible(fn () => $appointment->jenis === 'konsultasi'),

                    Placeholder::make('dokter_label')
                        ->label('Dokter')
                        ->content(fn (): HtmlString => new HtmlString(
                            '<span style="color:#111827;">' . e($appointment->dokter?->name ?? '-') . '</span>'
                        ))
                        ->visible(fn () => $appointment->jenis === 'konsultasi'),

                    Placeholder::make('terapis_label')
                        ->label('Terapis')
                        ->content(fn (): HtmlString => new HtmlString(
                            '<span style="color:#111827;">' . e($appointment->terapis?->name ?? '-') . '</span>'
                        ))
                        ->visible(fn () => $appointment->jenis === 'treatment'),

                    // Treatment & Bundle Recommendations (repeater)
                    Repeater::make('treatment_items')
                        ->label('Treatment / Bundle')
                        ->columnSpanFull()
                        ->addActionLabel('+ Tambah Treatment / Bundle')
                        ->defaultItems(0)
                        ->default(function () use ($appointment) {
                            return $appointment->items()
                                ->whereIn('item_type', ['treatment', 'bundle'])
                                ->get()
                                ->map(fn ($i) => [
                                    'item_type'    => $i->item_type,
                                    'treatment_id' => $i->treatment_id,
                                    'bundle_id'    => $i->bundle_id,
                                    'qty'          => (int) ($i->qty ?? 1),
                                ])
                                ->all();
                        })
                        ->reorderable(false)
                        ->reactive()
                        ->schema([
                            Grid::make(3)->schema([
                                Select::make('item_type')
                                    ->label('Tipe')
                                    ->options([
                                        'treatment' => 'Treatment',
                                        'bundle'    => 'Bundle',
                                    ])
                                    ->default('treatment')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set) => [
                                        $set('treatment_id', null),
                                        $set('bundle_id', null),
                                    ]),

                                Select::make('treatment_id')
                                    ->label('Treatment')
                                    ->options(fn () => Treatment::query()
                                        ->where('is_active', true)
                                        ->orderBy('nama')
                                        ->get()
                                        ->mapWithKeys(fn ($t) => [
                                            $t->treatment_id => $t->nama . ' — Rp ' . number_format((int) $t->harga, 0, ',', '.'),
                                        ])
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get) => $get('item_type') === 'treatment')
                                    ->required(fn (Get $get) => $get('item_type') === 'treatment')
                                    ->reactive(),

                                Select::make('bundle_id')
                                    ->label('Bundle')
                                    ->options(fn () => Bundle::query()
                                        ->where('is_active', true)
                                        ->orderBy('nama')
                                        ->get()
                                        ->mapWithKeys(function ($b) {
                                            $price = (int) ($b->harga_promo ?: $b->harga_normal);
                                            return [$b->bundle_id => $b->nama . ' — Rp ' . number_format($price, 0, ',', '.')];
                                        })
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get) => $get('item_type') === 'bundle')
                                    ->required(fn (Get $get) => $get('item_type') === 'bundle')
                                    ->reactive(),
                            ]),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                        ]),
                ]),

            // ── 3. PEMERIKSAAN AWAL ──
            Section::make('Pemeriksaan Awal')
                ->columns(1)
                ->schema([
                    Textarea::make('keluhan')
                        ->label('Keluhan Pasien')
                        ->rows(3)
                        ->placeholder('Catat keluhan utama pasien...'),

                    Textarea::make('diagnosa')
                        ->label('Diagnosa')
                        ->rows(3)
                        ->placeholder('Diagnosa dari dokter...'),
                ]),

            // ── 4. DOKUMENTASI & LAMPIRAN ──
            Section::make('Dokumentasi & Lampiran')
                ->columns(2)
                ->schema([
                    FileUpload::make('foto_sebelum_path')
                        ->label('Upload Before Photo')
                        ->helperText('JPG, PNG max 5MB')
                        ->disk('public')
                        ->directory('rekam-medis/sebelum')
                        ->image()
                        ->imageEditor()
                        ->maxSize(5120),

                    FileUpload::make('foto_sesudah_path')
                        ->label('Upload After Photo')
                        ->helperText('JPG, PNG max 5MB')
                        ->disk('public')
                        ->directory('rekam-medis/sesudah')
                        ->image()
                        ->imageEditor()
                        ->maxSize(5120),
                ]),

            // ── 5. CATATAN & REGIMEN ──
            Section::make('Catatan & Regimen')
                ->columns(1)
                ->schema([
                    Repeater::make('produk_rekomendasi_items')
                        ->label('Regimen / Produk Rekomendasi')
                        ->addActionLabel('+ Tambah Produk Regimen')
                        ->defaultItems(0)
                        ->reorderable(false)
                        ->schema([
                            Grid::make(3)->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->options(fn () => Product::query()
                                        ->where('is_active', true)
                                        ->orderBy('nama')
                                        ->get()
                                        ->mapWithKeys(fn ($p) => [
                                            $p->product_id => $p->nama . ' — Rp ' . number_format((int) $p->harga, 0, ',', '.'),
                                        ])
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->columnSpan(2),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                            ]),
                        ]),

                    Textarea::make('catatan_lanjutan')
                        ->label('Catatan Lanjutan')
                        ->rows(3)
                        ->placeholder('Instruksi follow-up atau catatan khusus...'),
                ]),

            // ── 6. PEMBAYARAN ──
            Section::make('Pembayaran')
                ->columns(2)
                ->schema([
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash'          => 'Cash',
                            'qris'          => 'QRIS',
                            'bank_transfer' => 'Transfer Bank',
                            'bca'           => 'BCA',
                            'bsi'           => 'BSI',
                            'mandiri'       => 'Mandiri',
                            'credit_card'   => 'Kartu Kredit',
                        ])
                        ->native(false)
                        ->placeholder('Pilih metode'),

                    Select::make('payment_status')
                        ->label('Status Pembayaran')
                        ->options([
                            'menunggu_pembayaran' => 'Menunggu Pembayaran',
                            'lunas'               => 'Sudah Dibayar (Lunas)',
                        ])
                        ->default('menunggu_pembayaran')
                        ->native(false)
                        ->required(),
                ]),
        ];
    }
}
