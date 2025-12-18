<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\JadwalPraktik;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'konsultasi' => 'Konsultasi',
                        'treatment' => 'Treatment',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set) => $set('jam_slot', null)),

                Select::make('dokter_id')
                    ->label('Dokter')
                    ->relationship(
                        name: 'dokter',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) =>
                            $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'dokter'))
                    )
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => $get('jenis') === 'konsultasi')
                    ->visible(fn (Get $get) => $get('jenis') === 'konsultasi')
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set) => $set('jam_slot', null)),

                Select::make('terapis_id')
                    ->label('Terapis')
                    ->relationship(
                        name: 'terapis',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) =>
                            $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'terapis'))
                    )
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => $get('jenis') === 'treatment')
                    ->visible(fn (Get $get) => $get('jenis') === 'treatment')
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set) => $set('jam_slot', null)),

                Select::make('treatment_id')
                    ->label('Treatment')
                    ->relationship('treatment', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => $get('jenis') === 'treatment')
                    ->visible(fn (Get $get) => $get('jenis') === 'treatment'),

                DateTimePicker::make('tanggal_jam')
                    ->label('Tanggal & Jam')
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->minDate(now())
                    ->reactive()
                    ->rule(function (Get $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            if (!$value) return;

                            $jenis = $get('jenis'); // konsultasi / treatment
                            if (!$jenis) return;

                            $petugasId = $jenis === 'treatment' ? $get('terapis_id') : $get('dokter_id');
                            if (!$petugasId) return;

                            // mapping hari Indo tanpa locale
                            $dt = Carbon::parse($value);
                            $mapHari = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu',7=>'minggu'];
                            $hari = $mapHari[$dt->dayOfWeekIso] ?? null;
                            if (!$hari) return;

                            $jenisLayanan = $jenis === 'treatment' ? 'treatment' : 'konsultasi';

                            $jadwal = JadwalPraktik::query()
                                ->where('user_id', $petugasId)
                                ->where('jenis_layanan', $jenisLayanan)
                                ->whereRaw('LOWER(hari) = ?', [$hari])
                                ->where('is_active', true)
                                ->first();

                            if (!$jadwal) {
                                $fail('Tidak ada jadwal praktik pada hari tersebut.');
                                return;
                            }

                            $mulai   = Carbon::parse($dt->format('Y-m-d') . ' ' . $jadwal->jam_mulai);
                            $selesai = Carbon::parse($dt->format('Y-m-d') . ' ' . $jadwal->jam_selesai);

                            if ($dt->lt($mulai) || $dt->gte($selesai)) {
                                $fail('Jam yang dipilih di luar jam praktik.');
                                return;
                            }

                            // harus pas slot
                            $durasi = (int) ($jadwal->durasi_slot_menit ?: 60);
                            $diffMenit = $mulai->diffInMinutes($dt);
                            if ($diffMenit % $durasi !== 0) {
                                $fail('Jam harus sesuai slot jadwal praktik.');
                                return;
                            }

                            // anti bentrok (slot sama)
                            $exists = Appointment::query()
                                ->where('jenis', $jenis)
                                ->where('tanggal_jam', $dt->format('Y-m-d H:i:s'))
                                ->whereIn('status', ['diajukan', 'disetujui'])
                                ->when($jenis === 'treatment',
                                    fn ($q) => $q->where('terapis_id', $petugasId),
                                    fn ($q) => $q->where('dokter_id', $petugasId),
                                )
                                ->exists();

                            if ($exists) {
                                $fail('Slot sudah terpakai.');
                            }
                        };
                    }),


                Select::make('user_id')
                    ->label('Pasien')
                    ->relationship(
                        name: 'pasien',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) =>
                            $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'pasien'))
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false))
                    ->default(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false)
                        ? Filament::auth()->id()
                        : null),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'diajukan' => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'batal' => 'Batal',
                        'selesai' => 'Selesai',
                        'tidak_hadir' => 'Tidak Hadir',
                    ])
                    ->required()
                    ->default('diajukan')
                    ->disabled(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false)),

                Select::make('sumber')
                    ->label('Sumber')
                    ->options(['online' => 'Online', 'offline' => 'Offline'])
                    ->required()
                    ->default('online'),

                Textarea::make('catatan_pasien')
                    ->label('Catatan Pasien')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('catatan_klinik')
                    ->label('Catatan Klinik')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false)),

                Hidden::make('dibuat_oleh_id')
                    ->default(fn () => Filament::auth()->id())
                    ->dehydrated(true),
            ])
            ->columns(2);
    }
}
