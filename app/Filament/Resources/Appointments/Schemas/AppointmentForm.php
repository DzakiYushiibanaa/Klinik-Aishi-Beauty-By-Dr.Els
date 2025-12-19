<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\JadwalPraktik;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                
                Select::make('patient_form_id')
                    ->label('Sumber Pra Konsul (Opsional)')
                    ->relationship('patientForm', 'patient_form_id')
                    ->searchable()
                    ->preload()
                    ->nullable(),

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

                Repeater::make('items')
                    ->label('Daftar Treatment / Bundle')
                    ->relationship('items')
                    ->schema([
                        Select::make('item_type')
                            ->label('Tipe')
                            ->options([
                                'treatment' => 'Treatment',
                                'bundle' => 'Bundle',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => [$set('treatment_id', null), $set('bundle_id', null)]),

                        Select::make('treatment_id')
                            ->label('Treatment')
                            ->relationship('treatment', 'nama')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('item_type') === 'treatment')
                            ->required(fn (Get $get) => $get('item_type') === 'treatment'),

                        Select::make('bundle_id')
                            ->label('Bundle')
                            ->relationship('bundle', 'nama')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('item_type') === 'bundle')
                            ->required(fn (Get $get) => $get('item_type') === 'bundle'),

                        TextInput::make('qty')
                            ->label('Qty')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel('Tambah Item')
                    ->reorderable(false)
                    ->visible(fn (Get $get) => $get('jenis') === 'treatment')
                    ->required(fn (Get $get) => $get('jenis') === 'treatment'),

                DateTimePicker::make('tanggal_jam')
                    ->label('Tanggal & Jam')
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->reactive()

                    // ✅ jangan minDate(now()) karena itu bikin appointment masa lalu gak bisa di-update statusnya
                    // ->minDate(now())  // HAPUS

                    ->rule(function (Get $get, ?Appointment $record) {
                        return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            if (!$value) return;

                            $dt = Carbon::parse($value);

                            // ✅ KUNCI: kalau EDIT dan tanggal_jam TIDAK berubah, skip semua validasi
                            // biar bisa update status (disetujui/selesai) walaupun appointment sudah lewat
                            if ($record && $record->tanggal_jam && $dt->equalTo(Carbon::parse($record->tanggal_jam))) {
                                return;
                            }

                            // ✅ kalau create / reschedule (nilai berubah), baru wajib >= sekarang
                            if ($dt->lt(now())) {
                                $fail('Tanggal & Jam tidak boleh di masa lalu.');
                                return;
                            }

                            $jenis = $get('jenis'); // konsultasi / treatment
                            if (!$jenis) return;

                            $petugasId = $jenis === 'treatment' ? $get('terapis_id') : $get('dokter_id');
                            if (!$petugasId) return;

                            // mapping hari Indo tanpa locale
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

                            // anti bentrok (slot sama) — exclude record sendiri saat edit
                            $exists = Appointment::query()
                                ->when($record, fn ($q) => $q->where('appointment_id', '!=', $record->appointment_id))
                                ->where('jenis', $jenis)
                                ->where('tanggal_jam', $dt->format('Y-m-d H:i:s'))
                                ->whereIn('status', ['diajukan', 'disetujui'])
                                ->when(
                                    $jenis === 'treatment',
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
