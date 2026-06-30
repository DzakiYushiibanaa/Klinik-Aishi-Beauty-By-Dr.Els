<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Filament\Resources\Appointments\Schemas\MulaiLayananForm;
use App\Models\Appointment;
use App\Models\JadwalPraktik;
use App\Models\User;
use App\Notifications\AppointmentStatusChanged; // Pastikan notifikasi di-import
use App\Services\Appointments\StartServiceFinalizer;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification; 
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Enums\Alignment;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

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
                    $query
                        ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
                        ->where(function (Builder $q) use ($id) {
                            $q->where('dokter_id', $id)
                                ->orWhereHas('items', fn (Builder $itemQuery) => $itemQuery
                                    ->where('handled_by_type', 'dokter')
                                    ->where('handled_by_id', $id));
                        });
                    return;
                }

                if ($user->hasRole('terapis')) {
                    $query
                        ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
                        ->whereHas('items', fn (Builder $q) => $q
                            ->where('handled_by_type', 'terapis')
                            ->where('handled_by_id', $id));
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
                        'akan_datang' => 'success',
                        'disetujui' => 'success',
                        'diproses' => 'info',
                        'batal' => 'danger',
                        'selesai' => 'gray',
                        'tidak_hadir' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'diajukan' => 'Diajukan',
                        'akan_datang' => 'Disetujui',
                        'diproses' => 'Disetujui',
                        'selesai' => 'Selesai',
                        'batal' => 'Dibatalkan',
                        'tidak_hadir' => 'Tidak Hadir',
                        'disetujui' => 'Disetujui',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
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
                        'batal'       => 'Dibatalkan',
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

                Action::make('atur_staff')
                    ->label('Atur Staff')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->tooltip('Atur dokter/terapis yang melayani')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Atur Staff Layanan')
                    ->modalDescription('Pilih dokter atau terapis yang akan menangani layanan ini.')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan Staff')
                    ->modalCancelActionLabel('Batal')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->visible(fn (Appointment $record): bool => self::canManageStaff()
                        && in_array($record->status, ['diajukan', 'akan_datang', 'disetujui'], true))
                    ->fillForm(fn (Appointment $record): array => [
                        'service_items' => self::staffAssignmentRows($record),
                    ])
                    ->schema(fn (Appointment $record): array => self::staffAssignmentSchema($record))
                    ->action(function (array $data, Appointment $record): void {
                        $result = self::saveStaffAssignment($record, $data);

                        if (! $result['ok']) {
                            Notification::make()
                                ->title('Gagal mengatur staff')
                                ->body($result['message'])
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Staff layanan berhasil diatur.')
                            ->success()
                            ->send();
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
                    ->modalDescription('Apakah Anda yakin ingin menyetujui booking ini?')
                    ->modalSubmitActionLabel('Setujui')
                    ->modalCancelActionLabel('Batal')
                    ->modalWidth('sm')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->visible(fn (Appointment $record) => $record->status === 'diajukan' && Filament::auth()->user()->hasRole('admin'))
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'disetujui']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Disetujui'));
                        }

                        // 🔔 WhatsApp ke pasien (best-effort).
                        try {
                            $fresh = Appointment::with(['items.treatment', 'items.bundle', 'items.consultation', 'dokter', 'terapis', 'pasien'])
                                ->find($record->appointment_id);
                            if ($fresh) {
                                app(FonnteService::class)->sendAppointmentApproved($fresh);
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Fonnte WA send gagal (admin setujui appointment): '.$e->getMessage());
                        }

                        Notification::make()->title('Janji temu berhasil disetujui')->success()->send();
                    }),

                // === TOMBOL TOLAK ===
                Action::make('tolak')
                    ->label('Tolak')
                    ->iconButton()
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->tooltip('Tolak Booking')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Booking')
                    ->modalDescription('Apakah Anda yakin ingin menolak booking ini?')
                    ->modalSubmitActionLabel('Tolak')
                    ->modalCancelActionLabel('Batal')
                    ->modalWidth('sm')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->visible(fn (Appointment $record) => $record->status === 'diajukan' && Filament::auth()->user()->hasRole('admin'))
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'batal']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Dibatalkan'));
                        }

                        // 🔔 WhatsApp ke pasien (best-effort).
                        try {
                            $fresh = Appointment::with(['items.treatment', 'items.bundle', 'items.consultation', 'dokter', 'terapis', 'pasien'])
                                ->find($record->appointment_id);
                            if ($fresh) {
                                app(FonnteService::class)->sendAppointmentCancelledByClinic($fresh, $fresh->cancel_reason);
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Fonnte WA send gagal (admin tolak appointment): '.$e->getMessage());
                        }

                        Notification::make()->title('Booking dibatalkan')->body('Pasien akan menerima notifikasi pembatalan.')->danger()->send();
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
                    ->modalDescription('Pastikan hasil pemeriksaan sudah benar sebelum menyelesaikan layanan.')
                    ->modalWidth('3xl')
                    ->modalSubmitActionLabel('Selesaikan Layanan')
                    ->modalCancelActionLabel('Batal')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->visible(fn (Appointment $record): bool => in_array($record->status, ['akan_datang', 'diproses', 'disetujui'], true)
                        && (
                            self::canManageStaff()
                            || self::userAssignedToAppointment($record)
                        ))
                    ->schema(fn (Appointment $record) => MulaiLayananForm::schema($record))
                    ->modalSubmitAction(function ($action) {
                        // Konfirmasi sebelum benar-benar finalisasi.
                        // Pesan-nya konsisten dengan flow workflow klinik:
                        // hasil pemeriksaan masuk ke rekam medis, tagihan
                        // diteruskan ke admin/frontdesk untuk pembayaran.
                        return $action->extraAttributes([
                            'wire:confirm' => 'Selesaikan Layanan?

Hasil pemeriksaan akan disimpan ke rekam medis pasien dan tagihan akan diteruskan ke proses pembayaran.',
                        ]);
                    })
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

                        Notification::make()
                            ->title('Layanan berhasil diselesaikan')
                            ->body('Hasil pemeriksaan tersimpan dan tagihan telah diteruskan ke proses pembayaran.')
                            ->success()
                            ->duration(6000)
                            ->send();
                    }),

                // === SIMPAN PROGRES (DRAFT) — tanpa membuat transaksi/notif ===
                ActionGroup::make([
                    Action::make('ubah_jadwal')
                        ->label('Ubah Jadwal')
                        ->icon('heroicon-o-calendar-days')
                        ->color('warning')
                        ->modalIcon('heroicon-o-calendar-days')
                        ->modalIconColor('warning')
                        ->modalHeading('Ubah Jadwal')
                        ->modalDescription(fn (Appointment $record): string => $record->jenis === 'treatment'
                            ? 'Pilih tanggal dan slot treatment baru. Setiap slot menampung maksimal 2 pasien.'
                            : 'Pilih jadwal baru sesuai hari praktik dokter.')
                        ->modalSubmitActionLabel('Ya, Ubah Jadwal')
                        ->modalCancelActionLabel('Batal')
                        ->modalWidth('2xl')
                        ->modalFooterActionsAlignment(Alignment::End)
                        ->steps(fn (Appointment $record): array => [
                            Step::make('Pilih Jadwal')
                                ->schema([
                                    Placeholder::make('reschedule_picker_header')
                                        ->hiddenLabel()
                                        ->content(fn () => new HtmlString(
                                            '<div style="display:grid; gap:6px; padding:14px 16px; border:1px solid #E8E0D8; border-radius:14px; background:linear-gradient(135deg,#FFFBF4 0%,#FAF8F5 100%);">'
                                            .'<div style="font-size:12px; font-weight:800; color:#B8956F; text-transform:uppercase; letter-spacing:.12em;">Pilih Jadwal Baru</div>'
                                            .'<div style="font-size:13px; color:#64748B; line-height:1.5;">Gunakan tanggal dan slot yang tersedia. Slot penuh dan waktu yang sudah lewat tidak bisa dipilih.</div>'
                                            .'</div>'
                                        )),

                                    ToggleButtons::make('new_tanggal')
                                        ->label('Pilih Tanggal Baru')
                                        ->options(fn (): array => self::rescheduleDateOptions($record))
                                        ->colors(fn (): array => array_fill_keys(array_keys(self::rescheduleDateOptions($record)), 'warning'))
                                        ->icons(fn (): array => array_fill_keys(array_keys(self::rescheduleDateOptions($record)), 'heroicon-o-calendar-days'))
                                        ->columns(['default' => 2, 'md' => 4])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($state, $set) => $set('new_jam', null))
                                        ->validationMessages([
                                            'required' => 'Pilih tanggal baru terlebih dahulu.',
                                        ]),

                                    ToggleButtons::make('new_jam')
                                        ->label(fn () => $record->jenis === 'treatment'
                                            ? 'Pilih Slot Treatment Baru (WIB)'
                                            : 'Pilih Jam Baru (WIB)')
                                        ->options(fn (Get $get): array => self::rescheduleSlotOptions($record, $get('new_tanggal')))
                                        ->colors(fn (Get $get): array => array_fill_keys(array_keys(self::rescheduleSlotOptions($record, $get('new_tanggal'))), 'warning'))
                                        ->icons(fn (Get $get): array => array_fill_keys(array_keys(self::rescheduleSlotOptions($record, $get('new_tanggal'))), 'heroicon-o-clock'))
                                        ->disableOptionWhen(fn (string $value, Get $get): bool => self::rescheduleSlotDisabled($record, $get('new_tanggal'), $value))
                                        ->inline(false)
                                        ->columns(['default' => 1, 'md' => 2])
                                        ->required()
                                        ->visible(fn (Get $get): bool => filled($get('new_tanggal')))
                                        ->validationMessages([
                                            'required' => 'Pilih slot atau jam baru terlebih dahulu.',
                                        ]),
                                ]),

                            Step::make('Konfirmasi')
                                ->schema([
                                    Placeholder::make('reschedule_confirm_header')
                                        ->hiddenLabel()
                                        ->content(fn () => new HtmlString(
                                            '<div style="display:grid; gap:6px; padding:14px 16px; border:1px solid #E8E0D8; border-radius:14px; background:linear-gradient(135deg,#FFFBF4 0%,#FAF8F5 100%);">'
                                            .'<div style="font-size:12px; font-weight:800; color:#B8956F; text-transform:uppercase; letter-spacing:.12em;">Konfirmasi Perubahan</div>'
                                            .'<div style="font-size:13px; color:#64748B; line-height:1.5;">Pastikan jadwal baru sudah benar sebelum disimpan.</div>'
                                            .'</div>'
                                        )),

                                    Placeholder::make('reschedule_confirmation')
                                        ->hiddenLabel()
                                        ->content(fn (Get $get) => self::rescheduleConfirmationHtml($record, $get('new_tanggal'), $get('new_jam'))),
                                ]),
                        ])
                        ->action(function (array $data, Appointment $record): void {
                            $result = self::rescheduleAppointment($record, $data);

                            if (! $result['ok']) {
                                Notification::make()
                                    ->title('Gagal mengubah jadwal')
                                    ->body($result['message'])
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Jadwal appointment berhasil diubah')
                                ->body('Status appointment tetap Disetujui.')
                                ->success()
                                ->send();
                        }),

                    Action::make('batalkan_appointment')
                        ->label('Batalkan Appointment')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->modalIcon('heroicon-o-x-circle')
                        ->modalIconColor('danger')
                        ->modalHeading('Batalkan Appointment')
                        ->modalDescription('Tambahkan alasan pembatalan jika diperlukan. Status appointment akan berubah menjadi Dibatalkan setelah dikonfirmasi.')
                        ->modalSubmitActionLabel('Konfirmasi')
                        ->modalCancelActionLabel('Batal')
                        ->modalWidth('md')
                        ->modalFooterActionsAlignment(Alignment::End)
                        ->schema([
                            Textarea::make('cancel_reason')
                                ->label('Alasan')
                                ->placeholder('Contoh: pasien meminta pembatalan jadwal')
                                ->rows(4)
                                ->autosize()
                                ->maxLength(1000),
                        ])
                        ->action(function (array $data, Appointment $record): void {
                            $record->update([
                                'status' => 'batal',
                                'canceled_by' => 'klinik',
                                'cancel_reason' => $data['cancel_reason'] ?? null,
                            ]);

                            if ($record->pasien) {
                                $record->pasien->notify(new AppointmentStatusChanged($record->fresh(), 'Dibatalkan'));
                            }

                            try {
                                $fresh = Appointment::with(['items.treatment', 'items.bundle', 'items.consultation', 'dokter', 'terapis', 'pasien'])
                                    ->find($record->appointment_id);
                                if ($fresh) {
                                    app(FonnteService::class)->sendAppointmentCancelledByClinic($fresh, $fresh->cancel_reason);
                                }
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning('Fonnte WA send gagal (batalkan appointment): '.$e->getMessage());
                            }

                            Notification::make()
                                ->title('Appointment dibatalkan')
                                ->body('Alasan tersimpan dan status appointment berubah menjadi Dibatalkan.')
                                ->danger()
                                ->send();
                        }),

                    Action::make('pasien_tidak_hadir')
                        ->label('Pasien Tidak Hadir')
                        ->icon('heroicon-o-user-minus')
                        ->color('warning')
                        ->modalIcon('heroicon-o-user-minus')
                        ->modalIconColor('warning')
                        ->modalHeading('Tandai Pasien Tidak Hadir')
                        ->modalDescription('Tambahkan catatan jika diperlukan. Status appointment akan berubah menjadi Tidak Hadir setelah dikonfirmasi.')
                        ->modalSubmitActionLabel('Konfirmasi')
                        ->modalCancelActionLabel('Batal')
                        ->modalWidth('md')
                        ->modalFooterActionsAlignment(Alignment::End)
                        ->schema([
                            Textarea::make('cancel_reason')
                                ->label('Catatan')
                                ->placeholder('Contoh: pasien tidak datang sampai jadwal berakhir')
                                ->rows(4)
                                ->autosize()
                                ->maxLength(1000),
                        ])
                        ->action(function (array $data, Appointment $record): void {
                            $record->update([
                                'status' => 'tidak_hadir',
                                'canceled_by' => null,
                                'cancel_reason' => $data['cancel_reason'] ?? null,
                            ]);

                            if ($record->pasien) {
                                $record->pasien->notify(new AppointmentStatusChanged($record->fresh(), 'Tidak Hadir'));
                            }

                            Notification::make()
                                ->title('Appointment ditandai tidak hadir')
                                ->body('Catatan tersimpan dan status appointment berubah menjadi Tidak Hadir.')
                                ->warning()
                                ->send();
                        }),
                ])
                    ->label('')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Aksi lainnya')
                    ->visible(fn (Appointment $record): bool => self::canManageStaff()
                        && ! in_array($record->status, ['batal', 'tidak_hadir', 'selesai'], true)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function rescheduleDateOptions(Appointment $record): array
    {
        $options = [];
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];

        for ($i = 0; $i < 14; $i++) {
            $date = now()->copy()->startOfDay()->addDays($i);

            if ($record->jenis !== 'treatment') {
                $hari = $mapHari[$date->dayOfWeekIso] ?? null;
                $hasSchedule = $hari && JadwalPraktik::query()
                    ->where('user_id', $record->dokter_id)
                    ->where('jenis_layanan', 'konsultasi')
                    ->whereRaw('LOWER(hari) = ?', [$hari])
                    ->where('is_active', true)
                    ->exists();

                if (! $hasSchedule) {
                    continue;
                }
            }

            $options[$date->toDateString()] = strtoupper($date->locale('id')->translatedFormat('D')) . ' ' . $date->format('d') . ' ' . $date->locale('id')->translatedFormat('M');
        }

        return $options;
    }

    private static function rescheduleSlotOptions(Appointment $record, ?string $date): array
    {
        if (! $date) {
            return [];
        }

        return $record->jenis === 'treatment'
            ? self::rescheduleTreatmentSlotOptions($record, $date)
            : self::rescheduleConsultationSlotOptions($record, $date);
    }

    private static function rescheduleSlotDisabled(Appointment $record, ?string $date, string $slot): bool
    {
        if (! $date || ! $slot) {
            return true;
        }

        $start = self::rescheduleSlotStart($date, $slot);
        if (! $start || $start->isPast()) {
            return true;
        }

        if ($record->jenis === 'treatment') {
            $booked = Appointment::query()
                ->whereKeyNot($record->appointment_id)
                ->where('jenis', 'treatment')
                ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
                ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
                ->count();

            return $booked >= 2;
        }

        return Appointment::query()
            ->whereKeyNot($record->appointment_id)
            ->where('jenis', 'konsultasi')
            ->where('dokter_id', $record->dokter_id)
            ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
            ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
            ->exists();
    }

    private static function rescheduleTreatmentSlotOptions(Appointment $record, string $date): array
    {
        $slots = [
            '08:00 - 10:00',
            '10:30 - 12:30',
            '13:00 - 15:00',
            '15:30 - 17:30',
        ];

        $options = [];
        foreach ($slots as $slot) {
            $start = self::rescheduleSlotStart($date, $slot);
            $booked = $start
                ? Appointment::query()
                    ->whereKeyNot($record->appointment_id)
                    ->where('jenis', 'treatment')
                    ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
                    ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
                    ->count()
                : 2;

            $sisa = max(0, 2 - $booked);
            $label = str_replace(':', '.', $slot);
            $options[$slot] = $label . ' | ' . ($start?->isPast() ? 'Lewat' : ($sisa > 0 ? "Sisa {$sisa} slot" : 'Penuh'));
        }

        return $options;
    }

    private static function rescheduleConsultationSlotOptions(Appointment $record, string $date): array
    {
        if (! $record->dokter_id) {
            return [];
        }

        $dt = \Carbon\Carbon::parse($date);
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        $hari = $mapHari[$dt->dayOfWeekIso] ?? null;

        $jadwal = $hari ? JadwalPraktik::query()
            ->where('user_id', $record->dokter_id)
            ->where('jenis_layanan', 'konsultasi')
            ->whereRaw('LOWER(hari) = ?', [$hari])
            ->where('is_active', true)
            ->first() : null;

        if (! $jadwal) {
            return [];
        }

        $start = \Carbon\Carbon::parse($date . ' ' . $jadwal->jam_mulai);
        $end = \Carbon\Carbon::parse($date . ' ' . $jadwal->jam_selesai);
        $duration = max(5, (int) ($jadwal->durasi_slot_menit ?: 60));
        $options = [];

        for ($cursor = $start->copy(); $cursor->lt($end); $cursor->addMinutes($duration)) {
            $value = $cursor->format('H:i');
            $isBooked = Appointment::query()
                ->whereKeyNot($record->appointment_id)
                ->where('jenis', 'konsultasi')
                ->where('dokter_id', $record->dokter_id)
                ->where('tanggal_jam', $cursor->format('Y-m-d H:i:s'))
                ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
                ->exists();

            $options[$value] = str_replace(':', '.', $value) . ' | ' . ($cursor->isPast() ? 'Lewat' : ($isBooked ? 'Terisi' : 'Tersedia'));
        }

        return $options;
    }

    private static function rescheduleSlotStart(string $date, string $slot): ?\Carbon\Carbon
    {
        $time = trim(explode('-', $slot)[0] ?? $slot);
        $time = str_replace('.', ':', $time);

        try {
            return \Carbon\Carbon::parse($date . ' ' . $time);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function rescheduleConfirmationHtml(Appointment $record, ?string $date, ?string $slot): HtmlString
    {
        $old = $record->tanggal_jam
            ? $record->tanggal_jam->locale('id')->translatedFormat('l, d M Y') . ' Jam ' . $record->tanggal_jam->format('H:i') . ' WIB'
            : '-';

        $newStart = $date && $slot ? self::rescheduleSlotStart($date, $slot) : null;
        $new = $newStart
            ? $newStart->locale('id')->translatedFormat('l, d M Y') . ' Jam ' . $newStart->format('H:i') . ' WIB'
            : 'Pilih tanggal dan slot terlebih dahulu.';

        return new HtmlString(
            '<div style="display:grid; gap:16px;">'
            .'<div style="padding:16px; border:1px solid #E8E0D8; border-radius:12px; background:#FAF8F5;">'
            .'<div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Jadwal Lama</div>'
            .'<div style="font-weight:700; color:#111827;">'.e($old).'</div>'
            .'<div style="height:1px; background:#E8E0D8; margin:14px 0;"></div>'
            .'<div style="font-size:11px; font-weight:700; color:#CFAF53; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Jadwal Baru</div>'
            .'<div style="font-weight:800; color:#CFAF53;">'.e($new).'</div>'
            .'</div>'
            .'<div style="padding:12px 14px; border:1px solid #FDE68A; border-radius:12px; background:#FFFBEB; color:#92400E; font-size:13px; line-height:1.5;">'
            .'Perubahan jadwal dari admin akan langsung menyimpan appointment sebagai <strong>Disetujui</strong>.'
            .'</div>'
            .'</div>'
        );
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    private static function rescheduleAppointment(Appointment $record, array $data): array
    {
        $date = $data['new_tanggal'] ?? null;
        $slot = $data['new_jam'] ?? null;

        if (! $date || ! $slot) {
            return ['ok' => false, 'message' => 'Pilih tanggal dan slot baru terlebih dahulu.'];
        }

        if (self::rescheduleSlotDisabled($record, $date, $slot)) {
            return ['ok' => false, 'message' => 'Slot yang dipilih sudah tidak tersedia. Silakan pilih slot lain.'];
        }

        $newDateTime = self::rescheduleSlotStart($date, $slot);
        if (! $newDateTime) {
            return ['ok' => false, 'message' => 'Format jadwal baru tidak valid.'];
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($record, $newDateTime) {
            $record->update([
                'tanggal_jam' => $newDateTime,
                'status' => 'disetujui',
                'cancel_reason' => null,
                'canceled_by' => null,
            ]);
        });

        $fresh = $record->fresh(['items.treatment', 'items.bundle', 'items.consultation', 'dokter', 'terapis', 'pasien']);

        if ($fresh?->pasien) {
            $fresh->pasien->notify(new AppointmentStatusChanged($fresh, 'Jadwal Diubah'));
        }

        try {
            if ($fresh) {
                app(FonnteService::class)->sendAppointmentRescheduled($fresh);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Fonnte WA send gagal (admin ubah jadwal appointment): '.$e->getMessage());
        }

        return ['ok' => true];
    }

    private static function canManageStaff(): bool
    {
        return Filament::auth()->user()?->hasAnyRole(['admin', 'frontdesk']) ?? false;
    }

    private static function staffAssignmentSchema(Appointment $record): array
    {
        return [
            Placeholder::make('appointment_summary')
                ->hiddenLabel()
                ->content(function () use ($record): HtmlString {
                    $appointment = $record->loadMissing(['pasien', 'items.treatment', 'items.consultation']);
                    $patient = $appointment->pasien?->name ?? '-';
                    $schedule = $appointment->tanggal_jam
                        ? $appointment->tanggal_jam->format('d M Y H:i')
                        : '-';

                    return new HtmlString(
                        '<div style="display:grid; gap:6px; padding:12px 14px; border:1px solid #E5E7EB; border-radius:8px; background:#F9FAFB;">'
                        .'<div style="font-weight:700; color:#111827;">'.e($patient).'</div>'
                        .'<div style="font-size:0.875rem; color:#4B5563;">Tanggal & jam: '.e($schedule).'</div>'
                        .'</div>'
                    );
                }),

            Repeater::make('service_items')
                ->label('Daftar Treatment / Konsultasi')
                ->schema([
                    Hidden::make('appointment_item_id'),
                    Hidden::make('item_type'),
                    Hidden::make('treatment_id'),
                    Hidden::make('consultation_id'),
                    Hidden::make('service_label')
                        ->dehydrated(false),
                    Hidden::make('handled_by_type')
                        ->dehydrated(),

                    Grid::make(['default' => 1, 'md' => 3])->schema([
                        Placeholder::make('service_name')
                            ->label('Layanan')
                            ->content(fn (Get $get): string => (string) ($get('service_label') ?? '-')),

                        Select::make('handled_by_id')
                            ->label('Dokter / Terapis')
                            ->placeholder(fn (Get $get): string => self::isDoctorOnlyAssignment($get)
                                ? 'Pilih dokter'
                                : 'Pilih petugas')
                            ->options(fn (Get $get): array => self::staffOptionsForAssignment($get))
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih dokter/terapis untuk semua layanan.',
                            ])
                            ->afterStateUpdated(fn ($state, $set) => $set('handled_by_type', $state ? self::resolveSelectedHandlerType((int) $state) : null))
                            ->dehydrated()
                            ->columnSpan(['md' => 2]),
                    ]),
                ])
                ->reorderable(false)
                ->addable(false)
                ->deletable(false)
                ->columnSpanFull(),
        ];
    }

    private static function staffAssignmentRows(Appointment $record): array
    {
        $record->loadMissing(['items.treatment.category', 'items.consultation']);

        return $record->items
            ->filter(fn ($item): bool => in_array($item->item_type, ['consultation', 'treatment'], true))
            ->map(function ($item) use ($record): array {
                $label = match ($item->item_type) {
                    'consultation' => $item->consultation?->nama ?? 'Konsultasi',
                    'treatment' => $item->treatment?->nama ?? 'Treatment',
                    default => 'Layanan',
                };

                $handledByType = $item->handled_by_type;
                $handledById = $item->handled_by_id;

                if ($item->item_type === 'consultation' && empty($handledById) && $record->dokter_id) {
                    $handledByType = 'dokter';
                    $handledById = $record->dokter_id;
                }

                return [
                    'appointment_item_id' => $item->appointment_item_id,
                    'item_type' => $item->item_type,
                    'treatment_id' => $item->treatment_id,
                    'consultation_id' => $item->consultation_id,
                    'service_label' => $label,
                    'handled_by_type' => $handledByType,
                    'handled_by_id' => $handledById,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    private static function saveStaffAssignment(Appointment $record, array $data): array
    {
        $rows = array_values((array) ($data['service_items'] ?? []));

        if ($rows === []) {
            return ['ok' => false, 'message' => 'Tidak ada layanan yang perlu diatur staff-nya.'];
        }

        $doctorId = null;
        $therapistId = null;

        foreach ($rows as $row) {
            if (empty($row['appointment_item_id']) || empty($row['handled_by_id'])) {
                return ['ok' => false, 'message' => 'Pilih dokter/terapis untuk semua layanan.'];
            }

            $handlerType = self::resolveSelectedHandlerType((int) $row['handled_by_id']);
            if (! $handlerType) {
                return ['ok' => false, 'message' => 'Pilih dokter/terapis untuk semua layanan.'];
            }

            $allowed = self::allowedHandlerTypesForAssignment($row);
            if (! in_array($handlerType, $allowed, true)) {
                return [
                    'ok' => false,
                    'message' => $allowed === ['dokter']
                        ? 'Treatment ini hanya dapat ditangani oleh dokter.'
                        : 'Pilih dokter/terapis untuk semua layanan.',
                ];
            }

            $record->items()
                ->whereKey((int) $row['appointment_item_id'])
                ->update([
                    'handled_by_type' => $handlerType,
                    'handled_by_id' => (int) $row['handled_by_id'],
                ]);

            if ($handlerType === 'dokter' && ! $doctorId) {
                $doctorId = (int) $row['handled_by_id'];
            }
            if ($handlerType === 'terapis' && ! $therapistId) {
                $therapistId = (int) $row['handled_by_id'];
            }
        }

        $record->update([
            'status' => 'disetujui',
            'dokter_id' => $doctorId,
            'terapis_id' => $therapistId,
        ]);

        return ['ok' => true];
    }

    private static function staffOptionsForAssignment(Get $get): array
    {
        $options = [];
        $allowed = self::allowedHandlerTypesForAssignment([
            'item_type' => $get('item_type'),
            'treatment_id' => $get('treatment_id'),
        ]);

        if (in_array('dokter', $allowed, true)) {
            foreach (self::activeStaffOptions('dokter') as $id => $name) {
                $options[$id] = 'Dr. '.$name.' — Dokter';
            }
        }

        if (in_array('terapis', $allowed, true)) {
            foreach (self::activeStaffOptions('terapis') as $id => $name) {
                $options[$id] = $name.' — Terapis';
            }
        }

        return $options;
    }

    private static function allowedHandlerTypesForAssignment(array $row): array
    {
        if (($row['item_type'] ?? null) === 'consultation') {
            return ['dokter'];
        }

        if (($row['item_type'] ?? null) !== 'treatment' || empty($row['treatment_id'])) {
            return [];
        }

        $treatment = \App\Models\Treatment::with('category')->find($row['treatment_id']);

        return $treatment?->category?->allowedHandlerTypes() ?? ['dokter', 'terapis'];
    }

    private static function activeStaffOptions(string $type): array
    {
        if ($type === 'dokter') {
            return User::role('dokter')
                ->whereHas('dokterProfile', fn (Builder $query) =>
                    $query->whereIn('status_praktik', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        if ($type === 'terapis') {
            return User::role('terapis')
                ->whereHas('terapisProfile', fn (Builder $query) =>
                    $query->whereIn('status_kerja', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        return [];
    }

    private static function resolveSelectedHandlerType(?int $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        $user = User::find($userId);
        if (! $user) {
            return null;
        }

        if ($user->hasRole('dokter')) {
            return 'dokter';
        }

        if ($user->hasRole('terapis')) {
            return 'terapis';
        }

        return null;
    }

    private static function isDoctorOnlyAssignment(Get $get): bool
    {
        return self::allowedHandlerTypesForAssignment([
            'item_type' => $get('item_type'),
            'treatment_id' => $get('treatment_id'),
        ]) === ['dokter'];
    }

    private static function userAssignedToAppointment(Appointment $record): bool
    {
        $user = Filament::auth()->user();
        if (! $user) {
            return false;
        }

        $type = $user->hasRole('dokter')
            ? 'dokter'
            : ($user->hasRole('terapis') ? 'terapis' : null);

        if (! $type) {
            return false;
        }

        if ($type === 'dokter' && (int) $record->dokter_id === (int) $user->getKey()) {
            return true;
        }

        if ($type === 'terapis' && (int) $record->terapis_id === (int) $user->getKey()) {
            return true;
        }

        return $record->items()
            ->where('handled_by_type', $type)
            ->where('handled_by_id', $user->getKey())
            ->exists();
    }
}
