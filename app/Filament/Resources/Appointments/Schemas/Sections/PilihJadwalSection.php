<?php

namespace App\Filament\Resources\Appointments\Schemas\Sections;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\Appointments\Support\AppointmentSlotResolver;

class PilihJadwalSection
{
    public static function make(): Section
    {
        return // ═══════════════════════════════════════════
                // Section 4: Pilih Jadwal
                // ═══════════════════════════════════════════
                Section::make('Pilih Jadwal')
                    ->schema([
                        DateTimePicker::make('tanggal_jam')
                            ->label('Tanggal & Jam')
                            ->required(fn(string $operation) => $operation === 'edit')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->hiddenOn('create')
                            ->dehydrated()
                            ->columnSpanFull(),

                        Placeholder::make('pilih_jadwal_title')
                            ->hiddenLabel()
                            ->hiddenOn('edit')
                            ->content(new HtmlString(
                                '<div style="margin-top: 8px; margin-bottom: 4px;">'
                                . '<div style="font-size:12px; font-weight:800; color:#B8956F; text-transform:uppercase; letter-spacing:.12em;">Pilih Jadwal Baru</div>'
                                . '<div style="font-size:13px; color:#64748B; line-height:1.5;">Gunakan tanggal dan slot yang tersedia. Slot penuh dan waktu yang sudah lewat tidak bisa dipilih.</div>'
                                . '</div>'
                            )),

                        ToggleButtons::make('new_tanggal')
                            ->label('Pilih Tanggal Baru')
                            ->options(fn(Get $get): array => AppointmentSlotResolver::dateOptions($get))
                            ->colors(fn(Get $get): array => array_fill_keys(array_keys(AppointmentSlotResolver::dateOptions($get)), 'warning'))
                            ->icons(fn(Get $get): array => array_fill_keys(array_keys(AppointmentSlotResolver::dateOptions($get)), 'heroicon-o-calendar-days'))
                            ->columns(['default' => 2, 'md' => 4])
                            ->required(fn(string $operation) => $operation === 'create')
                            ->live()
                            ->afterStateUpdated(fn($state, $set) => [
                                $set('new_jam', null),
                                $set('tanggal_jam', null),
                            ])
                            ->dehydrated(false)
                            ->validationMessages([
                                'required' => 'Pilih tanggal terlebih dahulu.',
                            ])
                            ->hiddenOn('edit'),

                        ToggleButtons::make('new_jam')
                            ->label(fn(Get $get) => $get('jenis') === 'treatment'
                                ? 'Pilih Slot Treatment Baru (WIB)'
                                : 'Pilih Jam Baru (WIB)')
                            ->options(fn(Get $get): array => AppointmentSlotResolver::slotOptions($get, $get('new_tanggal')))
                            ->colors(fn(Get $get): array => array_fill_keys(array_keys(AppointmentSlotResolver::slotOptions($get, $get('new_tanggal'))), 'warning'))
                            ->icons(fn(Get $get): array => array_fill_keys(array_keys(AppointmentSlotResolver::slotOptions($get, $get('new_tanggal'))), 'heroicon-o-clock'))
                            ->disableOptionWhen(fn(string $value, Get $get): bool => AppointmentSlotResolver::slotDisabled($get, $get('new_tanggal'), $value))
                            ->inline(false)
                            ->columns(['default' => 1, 'md' => 2])
                            ->required(fn(string $operation) => $operation === 'create')
                            ->visible(fn(Get $get, string $operation): bool => filled($get('new_tanggal')) && $operation === 'create')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state && $get('new_tanggal')) {
                                    $start = AppointmentSlotResolver::slotStart($get('new_tanggal'), $state);
                                    if ($start) {
                                        $set('tanggal_jam', $start->format('Y-m-d H:i:s'));
                                    }
                                }
                            })
                            ->dehydrated(false)
                            ->validationMessages([
                                'required' => 'Pilih slot atau jam terlebih dahulu.',
                            ])
                            ->hiddenOn('edit'),
                    ]);
    }
}
