<?php

namespace App\Filament\Resources\Appointments\Schemas\Sections;

use Filament\Schemas\Components\Section;
use App\Models\Appointment;
use App\Models\Consultation;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Appointments\Support\AppointmentHandlerResolver;

class RincianLayananSection
{
    public static function make(): Section
    {
        return // ═══════════════════════════════════════════
                // Section 3: Rincian Layanan
                // ═══════════════════════════════════════════
                Section::make('Rincian Layanan')
                    ->schema([
                        // ── Konsultasi ──
                        Select::make('consultation_id')
                            ->label('Pilih Konsultasi')
                            ->options(
                                fn() => Consultation::query()
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(fn($c) => [
                                        $c->consultation_id => $c->nama . ' — Rp ' . number_format($c->harga, 0, ',', '.') . ' (' . $c->durasi_menit . ' menit)'
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn(Get $get) => $get('jenis') === 'konsultasi')
                            ->visible(fn(Get $get) => $get('jenis') === 'konsultasi')
                            ->reactive()
                            ->afterStateHydrated(function (Select $component, ?Appointment $record) {
                                if (!$record)
                                    return;
                                $item = $record->items()
                                    ->where('item_type', 'consultation')
                                    ->whereNotNull('consultation_id')
                                    ->first();
                                if ($item) {
                                    $component->state($item->consultation_id);
                                }
                            }),

                        // ── Treatment Repeater ──
                        Repeater::make('items')
                            ->label('Daftar Treatment')
                            ->relationship('items')
                            ->schema([
                                Grid::make(3)->schema([
                                    Hidden::make('item_type')
                                        ->default('treatment'),

                                    Select::make('treatment_id')
                                        ->label('Treatment')
                                        ->relationship('treatment', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, $set, Get $get) {
                                            $handlerId = AppointmentHandlerResolver::defaultHandlerIdForTreatment((int) $state);
                                            $set('handled_by_id', $handlerId);
                                            $set('handled_by_type', $handlerId ? AppointmentHandlerResolver::resolveSelectedHandlerType($handlerId) : null);
                                        }),
                                ]),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),

                                Hidden::make('handled_by_type')
                                    ->dehydrated(),

                                Select::make('handled_by_id')
                                    ->label('Ditangani Oleh (Dokter / Terapis)')
                                    ->placeholder('Pilih petugas')
                                    ->options(fn(Get $get): array => AppointmentHandlerResolver::handlerOptionsForTreatment((int) $get('treatment_id')))
                                    ->searchable()
                                    ->native(false)
                                    ->visible(fn(Get $get) => !(Filament::auth()->user()?->hasRole('pasien') ?? false))
                                    ->required(fn(Get $get) => !(Filament::auth()->user()?->hasRole('pasien') ?? false))
                                    ->validationMessages([
                                        'required' => 'Pilih dokter/terapis yang menangani treatment ini.',
                                    ])
                                    ->disabled(fn(Get $get): bool => AppointmentHandlerResolver::isDoctorOnlyTreatment((int) $get('treatment_id')))
                                    ->dehydrated()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, $set) => $set('handled_by_type', $state ? AppointmentHandlerResolver::resolveSelectedHandlerType((int) $state) : null))
                                    ->helperText(fn(Get $get): ?string => AppointmentHandlerResolver::handlerHelperText((int) $get('treatment_id'))),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->reorderable(false)
                            ->visible(fn(Get $get) => $get('jenis') === 'treatment')
                            ->required(fn(Get $get) => $get('jenis') === 'treatment')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, $set) {
                                // TODO: method ensureSelectedTerapisEligible tidak ditemukan definisinya
                                // di seluruh codebase (per pengecekan saat refactor tanggal 9 Juli 2026).
                                // Kemungkinan leftover code atau bug lama. Perlu investigasi terpisah oleh
                                // pemilik project — TIDAK diperbaiki dalam refactor ini agar behavior
                                // tetap identik dengan sebelum refactor.
                                \App\Filament\Resources\Appointments\Schemas\AppointmentForm::ensureSelectedTerapisEligible($get, $set);
                                $set('tanggal_jam', null);
                            }),
                    ]);
    }
}
