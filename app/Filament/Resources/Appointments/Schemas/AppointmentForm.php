<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\BundleTreatmentItem;
use App\Models\JadwalPraktik;
use App\Models\Terapis;
use App\Models\Treatment;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AppointmentForm
{
    /**
     * Ambil min_level terbesar dari semua item yang dipilih (treatment langsung + treatment dalam bundle).
     * - default: 1
     * - kalau ada 1 aja butuh senior (2), hasilnya 2
     */
    private static function requiredMinLevel(Get $get): int
    {
        if ($get('jenis') !== 'treatment') {
            return 1;
        }

        $items = $get('items') ?? [];
        if (! is_array($items) || empty($items)) {
            return 1;
        }

        $max = 1;

        foreach ($items as $item) {
            $type = $item['item_type'] ?? null;

            // 1) Item treatment langsung
            if ($type === 'treatment' && ! empty($item['treatment_id'])) {
                $lvl = (int) (Treatment::query()->whereKey($item['treatment_id'])->value('min_level') ?? 1);
                $max = max($max, $lvl);
                continue;
            }

            // 2) Item bundle -> cek semua treatment yang ada di bundle
            if ($type === 'bundle' && ! empty($item['bundle_id'])) {
                $treatmentIds = BundleTreatmentItem::query()
                    ->where('bundle_id', $item['bundle_id'])
                    ->pluck('treatment_id')
                    ->filter()
                    ->all();

                if (! empty($treatmentIds)) {
                    $keyName = (new Treatment())->getKeyName(); // aman walau PK bukan "id"
                    $lvl = (int) (Treatment::query()->whereIn($keyName, $treatmentIds)->max('min_level') ?? 1);
                    $max = max($max, $lvl);
                }
            }
        }

        return $max;
    }

    /**
     * Konversi jabatan terapis (varchar) -> level angka.
     * - mengandung "senior" => 2
     * - mengandung "junior" => 1
     * - selain itu fallback 1
     */
    private static function terapisLevelFromUserId(?int $userId): int
    {
        if (! $userId) return 1;

        $jabatan = Terapis::query()
            ->where('user_id', $userId)
            ->value('jabatan');

        $j = strtolower(trim((string) $jabatan));

        if (str_contains($j, 'senior')) return 2;
        if (str_contains($j, 'junior')) return 1;

        return 1;
    }

    /**
     * Kalau terapis yang sudah dipilih ternyata tidak memenuhi requiredMinLevel, kosongkan.
     */
    private static function ensureSelectedTerapisEligible(Get $get, callable $set): void
    {
        $terapisId = $get('terapis_id');
        if (! $terapisId) return;

        $required = self::requiredMinLevel($get);
        $level = self::terapisLevelFromUserId((int) $terapisId);

        if ($level < $required) {
            $set('terapis_id', null);
        }
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pasien & Jenis Layanan')
                    ->columns(2)
                    ->schema([
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

                        Select::make('jenis')
                            ->label('Jenis')
                            ->options([
                                'konsultasi' => 'Konsultasi',
                                'treatment' => 'Treatment',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                // reset hal yang tergantung jenis
                                $set('dokter_id', null);
                                $set('terapis_id', null);
                                $set('tanggal_jam', null);
                            }),

                        Select::make('patient_form_id')
                            ->label('Sumber Pra Konsul (Opsional)')
                            ->relationship('patientForm', 'patient_form_id')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Detail Layanan')
                    ->columns(2)
                    ->schema([
                        // ==== KONSULTASI ====
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
                            ->afterStateUpdated(fn ($state, $set) => $set('tanggal_jam', null)),

                        // ==== TREATMENT (MULTI ITEM) ====
                        Repeater::make('items')
                            ->label('Daftar Treatment / Bundle')
                            ->relationship('items')
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('item_type')
                                        ->label('Tipe')
                                        ->options([
                                            'treatment' => 'Treatment',
                                            'bundle' => 'Bundle',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, $set) => [
                                            $set('treatment_id', null),
                                            $set('bundle_id', null),
                                        ]),

                                    Select::make('treatment_id')
                                        ->label('Treatment')
                                        ->relationship('treatment', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get) => $get('item_type') === 'treatment')
                                        ->required(fn (Get $get) => $get('item_type') === 'treatment')
                                        ->reactive(),

                                    Select::make('bundle_id')
                                        ->label('Bundle')
                                        ->relationship('bundle', 'nama')
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
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->reorderable(false)
                            ->visible(fn (Get $get) => $get('jenis') === 'treatment')
                            ->required(fn (Get $get) => $get('jenis') === 'treatment')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, $set) {
                                // kalau item berubah, terapis bisa jadi gak eligible
                                self::ensureSelectedTerapisEligible($get, $set);
                                // reset jadwal karena terapis eligible bisa berubah
                                $set('tanggal_jam', null);
                            })
                            ->columnSpanFull(),

                        Select::make('terapis_id')
                            ->label('Terapis')
                            ->relationship(
                                name: 'terapis',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get) {
                                    $required = self::requiredMinLevel($get);

                                    $query->role('terapis')
                                        ->whereHas('terapisProfile');

                                    // kalau ada item butuh senior, filter jadi senior saja
                                    if ($required >= 2) {
                                        $query->whereHas('terapisProfile', fn (Builder $q) =>
                                            $q->whereRaw('LOWER(jabatan) LIKE ?', ['%senior%'])
                                        );
                                    }

                                    return $query;
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('jenis') === 'treatment')
                            ->visible(fn (Get $get) => $get('jenis') === 'treatment')
                            ->helperText(fn (Get $get) =>
                                self::requiredMinLevel($get) >= 2
                                    ? 'Minimal level: Senior (karena ada treatment yang membutuhkan Senior).'
                                    : 'Minimal level: Junior.'
                            )
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, $set) {
                                self::ensureSelectedTerapisEligible($get, $set);
                                $set('tanggal_jam', null);
                            })
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (! $value) return;

                                    $required = self::requiredMinLevel($get);
                                    $level = self::terapisLevelFromUserId((int) $value);

                                    if ($level < $required) {
                                        $fail('Terapis yang dipilih tidak memenuhi level minimal untuk salah satu treatment.');
                                    }
                                };
                            }),
                    ]),

                Section::make('Jadwal')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('tanggal_jam')
                            ->label('Tanggal & Jam')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->reactive()
                            ->helperText('Pilih jadwal sesuai jam praktik dan slot yang tersedia.')
                            ->rule(function (Get $get, ?Appointment $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    if (! $value) return;

                                    $dt = Carbon::parse($value);

                                    // EDIT: kalau tanggal_jam tidak berubah, skip validasi jadwal
                                    if ($record && $record->tanggal_jam && $dt->equalTo(Carbon::parse($record->tanggal_jam))) {
                                        return;
                                    }

                                    // create/reschedule: wajib >= now
                                    if ($dt->lt(now())) {
                                        $fail('Tanggal & Jam tidak boleh di masa lalu.');
                                        return;
                                    }

                                    $jenis = $get('jenis');
                                    if (! $jenis) return;

                                    $petugasId = $jenis === 'treatment' ? $get('terapis_id') : $get('dokter_id');
                                    if (! $petugasId) return;

                                    $mapHari = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu',7=>'minggu'];
                                    $hari = $mapHari[$dt->dayOfWeekIso] ?? null;
                                    if (! $hari) return;

                                    $jenisLayanan = $jenis === 'treatment' ? 'treatment' : 'konsultasi';

                                    $jadwal = JadwalPraktik::query()
                                        ->where('user_id', $petugasId)
                                        ->where('jenis_layanan', $jenisLayanan)
                                        ->whereRaw('LOWER(hari) = ?', [$hari])
                                        ->where('is_active', true)
                                        ->first();

                                    if (! $jadwal) {
                                        $fail('Tidak ada jadwal praktik pada hari tersebut.');
                                        return;
                                    }

                                    $mulai   = Carbon::parse($dt->format('Y-m-d') . ' ' . $jadwal->jam_mulai);
                                    $selesai = Carbon::parse($dt->format('Y-m-d') . ' ' . $jadwal->jam_selesai);

                                    if ($dt->lt($mulai) || $dt->gte($selesai)) {
                                        $fail('Jam yang dipilih di luar jam praktik.');
                                        return;
                                    }

                                    $durasi = (int) ($jadwal->durasi_slot_menit ?: 60);
                                    $diffMenit = $mulai->diffInMinutes($dt);
                                    if ($diffMenit % $durasi !== 0) {
                                        $fail('Jam harus sesuai slot jadwal praktik.');
                                        return;
                                    }

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
                    ]),

                Section::make('Status & Catatan')
                    ->columns(2)
                    ->schema([
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
                    ]),
            ])
            ->columns(1);
    }
}
