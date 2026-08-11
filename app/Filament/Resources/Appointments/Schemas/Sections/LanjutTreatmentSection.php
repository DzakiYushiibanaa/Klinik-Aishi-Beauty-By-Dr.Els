<?php

namespace App\Filament\Resources\Appointments\Schemas\Sections;

use Filament\Schemas\Components\Section;
use App\Models\Appointment;
use App\Models\Treatment;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\Appointments\Support\AppointmentHandlerResolver;
use App\Filament\Resources\Appointments\Support\AppointmentItemSync;

class LanjutTreatmentSection
{
    public static function make(): Section
    {
        return // ═══════════════════════════════════════════
                // Section 4: Lanjut ke Treatment (Konsultasi only)
                // ═══════════════════════════════════════════
                Section::make('Lanjut ke Treatment')
                    ->description('Tambahkan treatment lanjutan yang akan dikerjakan setelah konsultasi. Harga dihitung otomatis dan akan masuk ke transaksi appointment ini.')
                    ->columnSpanFull()
                    ->visible(fn(Get $get) => $get('jenis') === 'konsultasi')
                    ->schema([
                        Toggle::make('lanjut_treatment')
                            ->label('Pasien lanjut ke treatment')
                            ->helperText('Aktifkan jika setelah konsultasi pasien langsung memerlukan treatment.')
                            ->reactive()
                            ->afterStateHydrated(function (Toggle $component, ?Appointment $record) {
                                if (!$record)
                                    return;
                                $hasFollowup = $record->items()
                                    ->whereIn('item_type', ['treatment'])
                                    ->exists();
                                $component->state($hasFollowup);
                            }),

                        Repeater::make('lanjut_items')
                            ->hiddenLabel()
                            ->visible(fn(Get $get) => (bool) $get('lanjut_treatment'))
                            ->reactive()
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Treatment')
                            ->reorderable(false)
                            ->afterStateHydrated(function (Repeater $component, ?Appointment $record) {
                                if (!$record)
                                    return;
                                $items = $record->items()
                                    ->whereIn('item_type', ['treatment'])
                                    ->get()
                                    ->map(fn($i) => [
                                        'item_type' => $i->item_type,
                                        'treatment_id' => $i->treatment_id,
                                        'qty' => (int) ($i->qty ?? 1),
                                        'handled_by_type' => $i->handled_by_type,
                                        'handled_by_id' => $i->handled_by_id,
                                    ])
                                    ->all();
                                if (!empty($items)) {
                                    $component->state($items);
                                }
                            })
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('item_type')
                                        ->label('Tipe')
                                        ->options([
                                            'treatment' => 'Treatment',
                                        ])
                                        ->default('treatment')
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, $set) => [
                                            $set('treatment_id', null),
                                        ]),

                                    Select::make('treatment_id')
                                        ->label('Treatment')
                                        ->options(
                                            fn() => Treatment::query()
                                                ->where('is_active', true)
                                                ->orderBy('nama')
                                                ->get()
                                                ->mapWithKeys(fn($t) => [
                                                    $t->treatment_id => $t->nama . ' — Rp ' . number_format((int) $t->harga, 0, ',', '.'),
                                                ])
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn(Get $get) => $get('item_type') === 'treatment')
                                        ->required(fn(Get $get) => $get('item_type') === 'treatment')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $handlerId = AppointmentHandlerResolver::defaultHandlerIdForTreatment((int) $state);
                                            $set('handled_by_id', $handlerId);
                                            $set('handled_by_type', $handlerId ? AppointmentHandlerResolver::resolveSelectedHandlerType($handlerId) : null);
                                        }),


                                ]),

                                Grid::make(3)->schema([
                                    TextInput::make('qty')
                                        ->label('Qty')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->reactive(),

                                    Hidden::make('handled_by_type')
                                        ->dehydrated(),

                                    Select::make('handled_by_id')
                                        ->label('Dokter / Terapis')
                                        ->placeholder('Pilih petugas')
                                        ->options(fn(Get $get): array => AppointmentHandlerResolver::handlerOptionsForTreatment((int) $get('treatment_id')))
                                        ->searchable()
                                        ->native(false)
                                        ->visible(fn(Get $get) => $get('item_type') === 'treatment' && !(Filament::auth()->user()?->hasRole('pasien') ?? false))
                                        ->required(fn(Get $get) => $get('item_type') === 'treatment' && !(Filament::auth()->user()?->hasRole('pasien') ?? false))
                                        ->validationMessages([
                                            'required' => 'Pilih dokter/terapis yang menangani treatment ini.',
                                        ])
                                        ->disabled(fn(Get $get): bool => AppointmentHandlerResolver::isDoctorOnlyTreatment((int) $get('treatment_id')))
                                        ->dehydrated()
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, $set) => $set('handled_by_type', $state ? AppointmentHandlerResolver::resolveSelectedHandlerType((int) $state) : null))
                                        ->helperText(fn(Get $get): ?string => AppointmentHandlerResolver::handlerHelperText((int) $get('treatment_id'))),

                                    Placeholder::make('harga_satuan')
                                        ->label('Harga Satuan')
                                        ->content(function (Get $get): HtmlString {
                                            $price = AppointmentItemSync::resolveItemPrice($get('item_type'), $get('treatment_id'));
                                            $text = $price !== null
                                                ? 'Rp ' . number_format($price, 0, ',', '.')
                                                : '—';
                                            return new HtmlString('<div style="font-weight:600; color:#111827;">' . $text . '</div>');
                                        }),

                                    Placeholder::make('subtotal')
                                        ->label('Subtotal')
                                        ->content(function (Get $get): HtmlString {
                                            $price = AppointmentItemSync::resolveItemPrice($get('item_type'), $get('treatment_id'));
                                            $qty = max(1, (int) ($get('qty') ?: 1));
                                            $text = $price !== null
                                                ? 'Rp ' . number_format($price * $qty, 0, ',', '.')
                                                : '—';
                                            return new HtmlString('<div style="font-weight:700; color:#B8956F;">' . $text . '</div>');
                                        }),
                                ]),
                            ])
                            ->columnSpanFull(),

                        Placeholder::make('lanjut_total')
                            ->label('Total Estimasi Treatment')
                            ->visible(fn(Get $get) => (bool) $get('lanjut_treatment'))
                            ->content(function (Get $get): HtmlString {
                                $items = $get('lanjut_items') ?? [];
                                $total = 0;
                                foreach ($items as $row) {
                                    $p = AppointmentItemSync::resolveItemPrice($row['item_type'] ?? null, $row['treatment_id'] ?? null);
                                    if ($p === null)
                                        continue;
                                    $total += $p * max(1, (int) ($row['qty'] ?? 1));
                                }
                                $fmt = 'Rp ' . number_format($total, 0, ',', '.');
                                return new HtmlString('
                                    <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:#FEF7E6; border:1px solid #F3E4C4; border-radius:10px;">
                                        <span style="color:#7A5A3A; font-size:0.875rem; font-weight:500;">Subtotal treatment akan ditagih pada transaksi appointment ini</span>
                                        <span style="color:#B8956F; font-size:1.125rem; font-weight:700; letter-spacing:-0.01em;">' . $fmt . '</span>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),
                    ]);
    }
}
