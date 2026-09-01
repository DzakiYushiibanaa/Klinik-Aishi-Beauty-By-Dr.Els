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

use App\Filament\Resources\Appointments\Tables\Concerns\HasReschedule;
use App\Filament\Resources\Appointments\Tables\Concerns\HasStaffAssignment;

class AppointmentsTable
{
    use HasReschedule;
    use HasStaffAssignment;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Ensure item relations are eager-loaded for the LAYANAN & MEDIS column.
                $query->with([
                    'items.treatment',
                    
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
                        ->whereIn('status', ['akan_datang', 'diproses', 'disetujui', 'selesai'])
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
                        ->whereIn('status', ['akan_datang', 'diproses', 'disetujui', 'selesai'])
                        ->whereHas('items', fn (Builder $q) => $q
                            ->where('handled_by_type', 'terapis')
                            ->where('handled_by_id', $id));
                    return;
                }
            })
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
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

                        'akan_datang' => 'success',
                        'disetujui' => 'success',
                        'diproses' => 'info',
                        'batal' => 'danger',
                        'selesai' => 'gray',
                        'tidak_hadir' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {

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
                        && in_array($record->status, ['akan_datang', 'disetujui'], true))
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
                    ->visible(false)
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'disetujui']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Disetujui'));
                        }

                        // 🔔 WhatsApp ke pasien (best-effort).
                        try {
                            $fresh = Appointment::with(['items.treatment', 'items.consultation', 'dokter', 'terapis', 'pasien'])
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
                    ->visible(false)
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'batal']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Dibatalkan'));
                        }

                        // 🔔 WhatsApp ke pasien (best-effort).
                        try {
                            $fresh = Appointment::with(['items.treatment', 'items.consultation', 'dokter', 'terapis', 'pasien'])
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
                        && auth()->user()->hasAnyRole(['dokter', 'terapis'])
                    )
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

                        $isNewOrder = $result['is_new_order'] ?? false;

                        Notification::make()
                            ->title('Layanan berhasil diselesaikan')
                            ->body($isNewOrder
                                ? 'Hasil pemeriksaan tersimpan dan tagihan telah diteruskan ke proses pembayaran.'
                                : 'Hasil pemeriksaan tersimpan. Tidak ada tagihan baru yang ditambahkan.')
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
                                $fresh = Appointment::with(['items.treatment', 'items.consultation', 'dokter', 'terapis', 'pasien'])
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

}