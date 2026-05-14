<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Resources\Appointments\Schemas\MulaiLayananForm;
use App\Models\Appointment;
use App\Notifications\AppointmentStatusChanged; // Pastikan notifikasi di-import
use App\Services\Appointments\StartServiceFinalizer;
use Filament\Actions\Action;
use Filament\Notifications\Notification; 
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Ensure item relations are eager-loaded for the LAYANAN & MEDIS column.
                $query->with([
                    'items.treatment',
                    'items.bundle',
                    'items.consultation',
                    'pasien',
                    'dokter',
                    'terapis',
                ]);

                $user = Filament::auth()->user();
                if (! $user) return;

                $id = $user->getKey(); 

                if ($user->hasRole('pasien')) {
                    $query->where('user_id', $id);
                    return;
                }

                if ($user->hasRole('dokter')) {
                    $query->where('dokter_id', $id);
                    return;
                }

                if ($user->hasRole('terapis')) {
                    $query->where('terapis_id', $id);
                    return;
                }
            })
            ->columns([
                TextColumn::make('pasien.name')
                    ->label('PASIEN & ID')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (Appointment $record): string => $record->appointment_id ?? '-'),
                
                TextColumn::make('tanggal_jam')
                    ->label('JADWAL')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d M Y'))
                    ->description(fn (Appointment $record) => \Carbon\Carbon::parse($record->tanggal_jam)->format('H:i \W\I\B'))
                    ->sortable(),

                TextColumn::make('items_summary')
                    ->label('LAYANAN & MEDIS')
                    ->weight('bold')
                    ->getStateUsing(function (Appointment $record): string {
                        // Group rows by type so "Konsultasi + 2 Treatment" reads nicely.
                        $parts = $record->items->map(function ($item) {
                            $qty = (int) ($item->qty ?? 1);
                            $suffix = $qty > 1 ? " x{$qty}" : '';

                            return match (true) {
                                $item->item_type === 'consultation' && $item->consultation => $item->consultation->nama . $suffix,
                                $item->item_type === 'treatment' && $item->treatment       => $item->treatment->nama . $suffix,
                                $item->item_type === 'bundle' && $item->bundle             => 'Bundle: ' . $item->bundle->nama . $suffix,
                                default                                                     => null,
                            };
                        })->filter()->values();

                        if ($parts->isEmpty()) {
                            // Fallback so the cell is never blank — show the booking type at least.
                            return $record->jenis
                                ? ucfirst(str_replace('_', ' ', $record->jenis))
                                : '—';
                        }

                        return $parts->implode(', ');
                    })
                    ->description(function (Appointment $record): string {
                        $dokter  = $record->dokter  ? 'Dr. ' . $record->dokter->name   : null;
                        $terapis = $record->terapis ? 'Terapis: ' . $record->terapis->name : null;

                        // Show only what's relevant for this appointment type.
                        $bits = match ($record->jenis) {
                            'konsultasi' => [$dokter],
                            'treatment'  => [$terapis],
                            default      => [$dokter, $terapis],
                        };

                        $bits = array_filter($bits);

                        return empty($bits) ? '—' : implode(' | ', $bits);
                    })
                    ->wrap()
                    ->limit(60),
                
                TextColumn::make('status')
                    ->label('STATUS')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diajukan' => 'warning',
                        'disetujui' => 'success',
                        'batal' => 'danger',
                        'selesai' => 'info',
                        'tidak_hadir' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->defaultSort('tanggal_jam', 'desc')
            ->filters([
                // === RANGE TANGGAL JADWAL ===
                Filter::make('tanggal_jam')
                    ->label('Rentang Tanggal')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->closeOnDateSelection(),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('tanggal_jam', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('tanggal_jam', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Dari ' . \Carbon\Carbon::parse($data['from'])->translatedFormat('d M Y'))
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Sampai ' . \Carbon\Carbon::parse($data['until'])->translatedFormat('d M Y'))
                                ->removeField('until');
                        }

                        return $indicators;
                    }),

                // === PRESET CEPAT ===
                SelectFilter::make('preset_tanggal')
                    ->label('Preset Cepat')
                    ->options([
                        'today'      => 'Hari Ini',
                        'tomorrow'   => 'Besok',
                        'this_week'  => 'Minggu Ini',
                        'this_month' => 'Bulan Ini',
                        'next_7'     => '7 Hari ke Depan',
                        'next_30'    => '30 Hari ke Depan',
                        'past'       => 'Sudah Lewat',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (! $value) return $query;

                        $now = \Carbon\Carbon::now();

                        return match ($value) {
                            'today'      => $query->whereDate('tanggal_jam', $now->toDateString()),
                            'tomorrow'   => $query->whereDate('tanggal_jam', $now->copy()->addDay()->toDateString()),
                            'this_week'  => $query->whereBetween('tanggal_jam', [
                                $now->copy()->startOfWeek(),
                                $now->copy()->endOfWeek(),
                            ]),
                            'this_month' => $query->whereBetween('tanggal_jam', [
                                $now->copy()->startOfMonth(),
                                $now->copy()->endOfMonth(),
                            ]),
                            'next_7'     => $query->whereBetween('tanggal_jam', [
                                $now->copy()->startOfDay(),
                                $now->copy()->addDays(7)->endOfDay(),
                            ]),
                            'next_30'    => $query->whereBetween('tanggal_jam', [
                                $now->copy()->startOfDay(),
                                $now->copy()->addDays(30)->endOfDay(),
                            ]),
                            'past'       => $query->where('tanggal_jam', '<', $now),
                            default      => $query,
                        };
                    }),

                // === STATUS ===
                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'diajukan'    => 'Diajukan',
                        'disetujui'   => 'Disetujui',
                        'selesai'     => 'Selesai',
                        'batal'       => 'Batal',
                        'tidak_hadir' => 'Tidak Hadir',
                    ]),

                // === JENIS LAYANAN ===
                SelectFilter::make('jenis')
                    ->label('Jenis Layanan')
                    ->options([
                        'konsultasi' => 'Konsultasi',
                        'treatment'  => 'Treatment',
                    ]),

                SelectFilter::make('dokter_id')
                    ->label('Filter Dokter')
                    ->relationship('dokter', 'name', fn (Builder $query) => $query->role('dokter'))
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Filament::auth()->user()?->hasRole('admin') ?? false),

                SelectFilter::make('terapis_id')
                    ->label('Filter Terapis')
                    ->relationship('terapis', 'name', fn (Builder $query) => $query->role('terapis'))
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Filament::auth()->user()?->hasRole('admin') ?? false),

                Filter::make('mine')
                    ->label('Janji Temu Saya')
                    ->query(function (Builder $query) {
                        $user = Filament::auth()->user();
                        if (! $user) return;
                        $id = $user->getKey();

                        if ($user->hasRole('dokter'))  return $query->where('dokter_id', $id);
                        if ($user->hasRole('terapis')) return $query->where('terapis_id', $id);
                        if ($user->hasRole('pasien'))  return $query->where('user_id', $id);
                    })
                    ->visible(fn () => (Filament::auth()->user()?->hasAnyRole(['dokter','terapis','pasien']) ?? false)),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                // === VIEW DETAIL ===
                Action::make('view_detail')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip('Lihat Detail Appointment')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading(fn (Appointment $record) => 'Detail Appointment #'
                        . str_pad((string) $record->appointment_id, 4, '0', STR_PAD_LEFT))
                    ->modalWidth('2xl')
                    ->modalCancelActionLabel('Tutup')
                    ->modalSubmitAction(false)
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->modalContent(function (Appointment $record) {
                        $appointment = Appointment::with([
                            'items.treatment',
                            'items.bundle',
                            'items.consultation',
                            'pasien.pasienProfile',
                            'dokter',
                            'terapis',
                        ])->find($record->appointment_id);

                        return view('filament.appointments.detail-slideover', compact('appointment'));
                    }),

                EditAction::make()
                    ->iconButton()
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Edit Appointment')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Save Changes')
                    ->modalCancelActionLabel('Cancel')
                    ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
                    ->mutateFormDataUsing(function (array $data, Appointment $record) {
                        // Strip non-column values before Filament writes the appointments row.
                        $data = AppointmentForm::captureAndStripConsultationId($data, $record->getKey());
                        $data = AppointmentForm::captureAndStripFollowupItems($data, $record->getKey());
                        return $data;
                    })
                    ->after(function (Appointment $record) {
                        AppointmentForm::syncConsultationItem(
                            $record->fresh(),
                            AppointmentForm::consumeConsultationId($record->getKey()),
                        );
                        AppointmentForm::syncFollowupItems($record->fresh(), $record->getKey());
                    }),

                // === TOMBOL SETUJUI ===
                Action::make('setujui')
                    ->label('Setujui')
                    ->iconButton()
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->tooltip('Setujui Booking')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Booking')
                    ->visible(fn (Appointment $record) => $record->status === 'diajukan' && Filament::auth()->user()->hasRole('admin'))
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'disetujui']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Disetujui'));
                        }

                        Notification::make()->title('Berhasil Disetujui')->success()->send();
                    }),

                // === TOMBOL TOLAK ===
                Action::make('tolak')
                    ->label('Tolak')
                    ->iconButton()
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->tooltip('Tolak Booking')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => $record->status === 'diajukan' && Filament::auth()->user()->hasRole('admin'))
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'batal']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Dibatalkan'));
                        }

                        Notification::make()->title('Booking Dibatalkan')->danger()->send();
                    }),

                // === MULAI LAYANAN ===
                // Buka modal besar untuk melakukan pemeriksaan, upload foto, pilih treatment,
                // & finalisasi (buat rekam medis + order + notif ke pasien).
                Action::make('mulai_layanan')
                    ->label('Mulai Layanan')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->tooltip('Mulai & selesaikan layanan')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Mulai Layanan')
                    ->modalDescription(fn (Appointment $record) => new \Illuminate\Support\HtmlString(
                        '<div style="padding:12px 14px; background:#FEF7E6; border:1px solid #F3E4C4; border-radius:10px; color:#7A5A3A; font-size:0.875rem; line-height:1.5;">'
                        . '<strong>Perhatian:</strong> Ketika Anda menekan <strong>Selesaikan Layanan</strong>, sistem akan otomatis membuat <strong>Rekam Medis</strong> dan <strong>Transaksi</strong>, lalu mengirim notifikasi + email invoice ke pasien ('
                        . e($record->pasien?->email ?? '—') . ').'
                        . '</div>'
                    ))
                    ->modalWidth('3xl')
                    ->modalSubmitActionLabel('Selesaikan Layanan')
                    ->modalCancelActionLabel('Cancel')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->visible(fn (Appointment $record) => $record->status === 'disetujui'
                        && (Filament::auth()->user()?->hasAnyRole(['admin', 'dokter', 'terapis']) ?? false))
                    ->schema(fn (Appointment $record) => MulaiLayananForm::schema($record))
                    ->action(function (array $data, Appointment $record) {
                        try {
                            $result = app(StartServiceFinalizer::class)->complete($record, $data);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('StartServiceFinalizer failed', [
                                'appointment_id' => $record->appointment_id,
                                'error'          => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Gagal menyelesaikan layanan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        $kodeOrder   = $result['order']?->kode_order;
                        $noKunjungan = $result['rekam_medis']?->no_kunjungan;

                        Notification::make()
                            ->title('Layanan Selesai')
                            ->body(
                                'Rekam medis ' . ($noKunjungan ?: '-')
                                . ($kodeOrder ? ' & transaksi ' . $kodeOrder : '')
                                . ' berhasil dibuat. Notifikasi + email invoice telah dikirim ke pelanggan.'
                            )
                            ->success()
                            ->duration(7000)
                            ->send();
                    }),

                // === SIMPAN PROGRES (DRAFT) — tanpa membuat transaksi/notif ===
                Action::make('simpan_progress_layanan')
                    ->label('Simpan Draft Pemeriksaan')
                    ->iconButton()
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->tooltip('Simpan hasil pemeriksaan sementara (tanpa membuat transaksi)')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Simpan Draft Pemeriksaan')
                    ->modalWidth('3xl')
                    ->modalSubmitActionLabel('Simpan Draft')
                    ->modalCancelActionLabel('Cancel')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->visible(fn (Appointment $record) => $record->status === 'disetujui'
                        && (Filament::auth()->user()?->hasAnyRole(['admin', 'dokter', 'terapis']) ?? false))
                    ->schema(fn (Appointment $record) => MulaiLayananForm::schema($record))
                    ->action(function (array $data, Appointment $record) {
                        app(StartServiceFinalizer::class)->saveDraft($record, $data);

                        Notification::make()
                            ->title('Draft tersimpan')
                            ->body('Anda dapat melanjutkan pemeriksaan ini nanti.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}