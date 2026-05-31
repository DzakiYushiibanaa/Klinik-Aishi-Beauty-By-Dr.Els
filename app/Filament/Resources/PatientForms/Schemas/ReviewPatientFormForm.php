<?php

namespace App\Filament\Resources\PatientForms\Schemas;

use App\Models\Bundle;
use App\Models\PatientForm;
use App\Models\Product;
use App\Models\Treatment;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

/**
 * Schema untuk slide-over panel "Mulai Review Pra-Konsultasi".
 *
 * Layout:
 *   - LEFT  → Section informasi pasien (read-only Placeholder).
 *   - RIGHT → Section input dokter (analisa, rekomendasi treatment & produk,
 *             catatan tambahan).
 *
 * Filament Schema otomatis layout dua kolom kalau kita pakai Grid::make(2),
 * jadi UI akan terasa seperti workflow review modern tanpa perlu blade
 * custom yang kompleks.
 */
class ReviewPatientFormForm
{
    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public static function schema(PatientForm $form): array
    {
        return [
            Grid::make(['default' => 1, 'md' => 2])->schema([

                // ════════════ LEFT — DATA PASIEN (readonly) ════════════
                Grid::make(1)->schema([
                    Section::make('Identitas Pasien')
                        ->compact()
                        ->schema([
                            Placeholder::make('pasien_nama')
                                ->label('Nama')
                                ->content(fn (): HtmlString => self::badgeText(
                                    $form->pasien?->user?->name ?: ($form->pasien?->nama ?: '—'),
                                    bold: true
                                )),
                            Placeholder::make('pasien_rm')
                                ->label('No. Rekam Medis')
                                ->content(fn (): HtmlString => self::badgeText(
                                    $form->pasien?->no_rekam_medis ?: '—'
                                )),
                            Placeholder::make('keluhan_label')
                                ->label('Keluhan Utama')
                                ->content(fn (): HtmlString => self::longText($form->keluhan_utama)),
                        ]),

                    Section::make('Foto Wajah')
                        ->compact()
                        ->schema([
                            Placeholder::make('foto_grid')
                                ->hiddenLabel()
                                ->content(fn (): HtmlString => self::photoGrid($form)),
                        ])
                        ->visible(fn () => $form->foto_depan_path || $form->foto_kiri_path || $form->foto_kanan_path),

                    Section::make('Kondisi Kulit')
                        ->compact()
                        ->schema([
                            Placeholder::make('jenis_kulit')
                                ->label('Jenis Kulit')
                                ->content(fn (): HtmlString => self::badgeText($form->jenis_kulit ?: '—')),
                            Placeholder::make('golongan_darah')
                                ->label('Golongan Darah')
                                ->content(fn (): HtmlString => self::badgeText($form->golongan_darah ?: '—')),
                            Placeholder::make('riwayat_penyakit')
                                ->label('Riwayat Penyakit')
                                ->content(fn (): HtmlString => self::longText($form->riwayat_penyakit)),
                        ]),

                    Section::make('Riwayat Skincare & Treatment')
                        ->compact()
                        ->schema([
                            Placeholder::make('skincare_sebelumnya')
                                ->label('Skincare Sebelumnya')
                                ->content(fn (): HtmlString => self::longText($form->skincare_sebelumnya)),
                            Placeholder::make('periode_skincare')
                                ->label('Periode Pakai (bulan)')
                                ->content(fn (): HtmlString => self::badgeText(
                                    $form->periode_skincare_bulan ? $form->periode_skincare_bulan.' bulan' : '—'
                                )),
                            Placeholder::make('riwayat_treatment')
                                ->label('Pernah Treatment')
                                ->content(fn (): HtmlString => new HtmlString(
                                    self::treatmentHistory($form)
                                )),
                        ]),
                ]),

                // ════════════ RIGHT — INPUT DOKTER ════════════
                Grid::make(1)->schema([
                    Section::make('Analisa & Rekomendasi Dokter')
                        ->description('Hanya bagian ini yang dapat Anda isi. Data pasien di sebelah kiri bersifat read-only.')
                        ->schema([
                            Textarea::make('diagnosa')
                                ->label('Diagnosa Dokter')
                                ->placeholder('Tuliskan diagnosa kondisi kulit pasien…')
                                ->rows(4)
                                ->required()
                                ->maxLength(2000)
                                ->autosize(),

                            Repeater::make('treatment_recommendations')
                                ->label('Rekomendasi Treatment')
                                ->addActionLabel('+ Tambah Treatment')
                                ->reorderable(false)
                                ->defaultItems(0)
                                ->schema([
                                    Select::make('item_type')
                                        ->label('Tipe')
                                        ->options([
                                            'treatment' => 'Treatment',
                                            'bundle'    => 'Bundle',
                                        ])
                                        ->default('treatment')
                                        ->reactive()
                                        ->required()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $set('treatment_id', null);
                                            $set('bundle_id', null);
                                        }),

                                    Select::make('treatment_id')
                                        ->label('Treatment')
                                        ->options(fn () => Treatment::query()
                                            ->where('is_active', true)
                                            ->orderBy('nama')
                                            ->pluck('nama', 'treatment_id')
                                            ->all()
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn ($get) => ($get('item_type') ?? 'treatment') === 'treatment')
                                        ->required(fn ($get) => ($get('item_type') ?? 'treatment') === 'treatment'),

                                    Select::make('bundle_id')
                                        ->label('Bundle')
                                        ->options(fn () => Bundle::query()
                                            ->where('is_active', true)
                                            ->orderBy('nama')
                                            ->pluck('nama', 'bundle_id')
                                            ->all()
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn ($get) => $get('item_type') === 'bundle')
                                        ->required(fn ($get) => $get('item_type') === 'bundle'),

                                    Textarea::make('catatan')
                                        ->label('Catatan (opsional)')
                                        ->rows(2)
                                        ->maxLength(255),
                                ]),

                            Repeater::make('product_recommendations')
                                ->label('Rekomendasi Skincare / Produk')
                                ->addActionLabel('+ Tambah Produk')
                                ->reorderable(false)
                                ->defaultItems(0)
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Produk')
                                        ->options(fn () => Product::query()
                                            ->where('is_active', true)
                                            ->orderBy('nama')
                                            ->pluck('nama', 'product_id')
                                            ->all()
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Textarea::make('catatan')
                                        ->label('Aturan Pakai / Catatan')
                                        ->placeholder('Mis. Pagi & malam setelah cuci wajah')
                                        ->rows(2)
                                        ->maxLength(255),
                                ]),

                            Textarea::make('catatan_tambahan')
                                ->label('Catatan Tambahan (opsional)')
                                ->placeholder('Pesan singkat untuk pasien (mis. tips kebersihan kulit, pantangan, dsb.)')
                                ->rows(3)
                                ->maxLength(1000)
                                ->autosize(),
                        ]),
                ]),
            ]),
        ];
    }

    /**
     * Default values untuk form ketika dokter membuka panel.
     * Ambil rekomendasi yang sudah disimpan kalau dokter sebelumnya
     * sempat menyimpan progress (atau ingin merevisi).
     */
    public static function defaults(PatientForm $form): array
    {
        $form->loadMissing(['rekomendasiItems']);

        $treatments = $form->rekomendasiItems
            ->filter(fn ($i) => $i->treatment_id || $i->bundle_id)
            ->map(fn ($i) => [
                'item_type'    => $i->treatment_id ? 'treatment' : 'bundle',
                'treatment_id' => $i->treatment_id,
                'bundle_id'    => $i->bundle_id,
                'catatan'      => $i->catatan,
            ])
            ->values()
            ->all();

        $products = $form->rekomendasiItems
            ->filter(fn ($i) => $i->product_id)
            ->map(fn ($i) => [
                'product_id' => $i->product_id,
                'catatan'    => $i->catatan,
            ])
            ->values()
            ->all();

        return [
            'diagnosa'                 => $form->diagnosa,
            'catatan_tambahan'         => $form->catatan_tambahan,
            'treatment_recommendations'=> $treatments,
            'product_recommendations'  => $products,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // Helpers — mini-renderer untuk Placeholder readonly
    // ═══════════════════════════════════════════════════════════

    private static function badgeText(string $text, bool $bold = false): HtmlString
    {
        $weight = $bold ? '700' : '500';
        return new HtmlString(
            '<span style="display:inline-block; padding:4px 10px; background:#FAF8F5; border:1px solid #EDE8E3; border-radius:8px; font-size:0.875rem; color:#1A1A1A; font-weight:'.$weight.';">'
            .e($text).'</span>'
        );
    }

    private static function longText(?string $text): HtmlString
    {
        $value = trim((string) $text);
        if ($value === '') {
            return new HtmlString('<span style="color:#94A3B8; font-style:italic;">Belum diisi.</span>');
        }
        return new HtmlString(
            '<div style="white-space:pre-wrap; font-size:0.875rem; color:#1A1A1A; line-height:1.55;">'
            .nl2br(e($value)).'</div>'
        );
    }

    private static function treatmentHistory(PatientForm $form): string
    {
        $items = [];
        $items['Pernah Facial']      = (bool) $form->pernah_facial;
        $items['Pernah Skin Booster']= (bool) $form->pernah_skin_booster;
        $items['Pernah Laser']       = (bool) $form->pernah_laser;
        $items['Pernah Inject/Infus']= (bool) $form->pernah_inject_infus;

        $html = '<div style="display:flex; flex-wrap:wrap; gap:6px;">';
        foreach ($items as $label => $val) {
            $bg = $val ? '#D1FAE5' : '#F1F5F9';
            $color = $val ? '#065F46' : '#64748B';
            $icon = $val ? '✓' : '✗';
            $html .= '<span style="padding:4px 10px; border-radius:9999px; background:'.$bg.'; color:'.$color.'; font-size:0.75rem; font-weight:600;">'
                .$icon.' '.e($label).'</span>';
        }
        $html .= '</div>';
        return $html;
    }

    private static function photoGrid(PatientForm $form): HtmlString
    {
        $cards = [];
        foreach ([
            ['label' => 'Depan',  'path' => $form->foto_depan_path],
            ['label' => 'Kiri',   'path' => $form->foto_kiri_path],
            ['label' => 'Kanan',  'path' => $form->foto_kanan_path],
        ] as $row) {
            if (! $row['path']) continue;
            $url = Storage::disk('public')->url($row['path']);
            $cards[] = '
                <a href="'.e($url).'" target="_blank" rel="noopener" style="display:block; flex:1 1 0;">
                    <div style="font-size:0.7rem; font-weight:700; color:#8A8078; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">'.e($row['label']).'</div>
                    <img src="'.e($url).'" alt="Foto '.e($row['label']).'" style="width:100%; aspect-ratio:3/4; object-fit:cover; border-radius:10px; border:1px solid #EDE8E3;" />
                </a>
            ';
        }

        if (empty($cards)) {
            return new HtmlString('<span style="color:#94A3B8; font-style:italic;">Belum ada foto wajah.</span>');
        }

        return new HtmlString(
            '<div style="display:flex; gap:10px; flex-wrap:wrap;">'.implode('', $cards).'</div>'
        );
    }
}
