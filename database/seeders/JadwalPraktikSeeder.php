<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use Spatie\Permission\Models\Role;

class JadwalPraktikSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = Carbon::now();

            // Guard Spatie (umumnya web)
            $guard = config('auth.defaults.guard', 'web');

            // Pastikan role ada
            Role::firstOrCreate(['name' => 'dokter', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'terapis', 'guard_name' => $guard]);

            $days = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];

            $jamMulai   = '09:00:00';
            $jamSelesai = '18:30:00';

            // =========================
            // 3 dokter + 3 terapis
            // =========================
            $dokters = [
                ['name' => 'Dr. Alya Pratama',    'email' => 'dokter1@klinik.test'],
                ['name' => 'Dr. Bella Santoso',   'email' => 'dokter2@klinik.test'],
                ['name' => 'Dr. Citra Wulandari', 'email' => 'dokter3@klinik.test'],
            ];

            $terapis = [
                ['name' => 'Terapis Fajar', 'email' => 'terapis1@klinik.test'],
                ['name' => 'Terapis Gita',  'email' => 'terapis2@klinik.test'],
                ['name' => 'Terapis Hana',  'email' => 'terapis3@klinik.test'],
            ];

            $dokterUserIds = [];
            $terapisUserIds = [];

            // ===== Dokter: upsert user + sync role
            foreach ($dokters as $i => $u) {
                $user = $this->upsertUserEloquent($u['name'], $u['email'], $now);

                // Spatie: set role dokter (overwrite roles lain)
                $user->syncRoles(['dokter']);

                $dokterUserIds[] = (int) $user->getKey();

                // Optional: profile dokter jika tabel ada
                $this->upsertDokterProfileIfExists((int) $user->getKey(), $i + 1, $now);
            }

            // ===== Terapis: upsert user + sync role
            foreach ($terapis as $i => $u) {
                $user = $this->upsertUserEloquent($u['name'], $u['email'], $now);

                $user->syncRoles(['terapis']);

                $terapisUserIds[] = (int) $user->getKey();

                // Optional: profile terapis jika tabel ada
                $this->upsertTerapisProfileIfExists((int) $user->getKey(), $i + 1, $now);
            }

            // =========================
            // JADWAL PRAKTIK
            // =========================

            // Dokter: konsultasi (60) + treatment (120) untuk tiap hari
            foreach ($dokterUserIds as $userId) {
                foreach ($days as $hari) {
                    $this->upsertJadwal($userId, 'konsultasi', $hari, $jamMulai, $jamSelesai, 60, $now);
                    $this->upsertJadwal($userId, 'treatment',   $hari, $jamMulai, $jamSelesai, 120, $now);
                }
            }

            // Terapis: hanya treatment (120) untuk tiap hari
            foreach ($terapisUserIds as $userId) {
                foreach ($days as $hari) {
                    $this->upsertJadwal($userId, 'treatment', $hari, $jamMulai, $jamSelesai, 120, $now);
                }
            }

            // =========================
            // (Opsional) Bersihin jadwal untuk akun seed yang "lebih dari 3"
            // Kalau sebelumnya kamu pernah seed dokter4/5 & terapis4/5 dan mau dihapus:
            // Uncomment bagian ini.
            // =========================
            /*
            $emailsToDelete = [
                'dokter4@klinik.test','dokter5@klinik.test',
                'terapis4@klinik.test','terapis5@klinik.test',
            ];

            $idsToDelete = User::whereIn('email', $emailsToDelete)->pluck((new User)->getKeyName())->toArray();
            if (!empty($idsToDelete)) {
                DB::table('jadwal_praktik')->whereIn('user_id', $idsToDelete)->delete();
                DB::table('model_has_roles')->whereIn('model_id', $idsToDelete)->delete();
                User::whereIn('email', $emailsToDelete)->delete();
            }
            */
        });
    }

    private function upsertUserEloquent(string $name, string $email, Carbon $now): User
    {
        /** @var User $user */
        $user = User::query()->where('email', $email)->first() ?? new User();

        $user->name = $name;
        $user->email = $email;

        // password default hanya untuk user baru
        if (!$user->exists) {
            $user->password = Hash::make('password123');
        }

        if (Schema::hasColumn('users', 'phone') && empty($user->phone)) {
            $user->phone = '08' . random_int(111111111, 999999999);
        }

        if (Schema::hasColumn('users', 'tanggal_lahir') && empty($user->tanggal_lahir)) {
            $user->tanggal_lahir = '2000-01-01';
        }

        // ini yang kamu tanyain: jenis_kelamin pasti perempuan jika kolomnya ada
        if (Schema::hasColumn('users', 'jenis_kelamin')) {
            $user->jenis_kelamin = 'perempuan';
        }

        if (Schema::hasColumn('users', 'email_verified_at') && empty($user->email_verified_at)) {
            $user->email_verified_at = $now;
        }

        $user->save();

        return $user;
    }

    private function upsertDokterProfileIfExists(int $userId, int $no, Carbon $now): void
    {
        if (!Schema::hasTable('dokter')) return;

        $exists = DB::table('dokter')->where('user_id', $userId)->first();
        if ($exists) return;

        $payload = ['user_id' => $userId];

        if (Schema::hasColumn('dokter', 'kode_dokter')) {
            $payload['kode_dokter'] = sprintf('DKR-%03d', $no);
        }
        if (Schema::hasColumn('dokter', 'spesialis')) {
            $payload['spesialis'] = 'estetika';
        }
        if (Schema::hasColumn('dokter', 'pengalaman_tahun')) {
            $payload['pengalaman_tahun'] = random_int(2, 10);
        }
        if (Schema::hasColumn('dokter', 'status_praktik')) {
            $payload['status_praktik'] = 'aktif';
        }
        if (Schema::hasColumn('dokter', 'created_at')) $payload['created_at'] = $now;
        if (Schema::hasColumn('dokter', 'updated_at')) $payload['updated_at'] = $now;

        DB::table('dokter')->insert($payload);
    }

    private function upsertTerapisProfileIfExists(int $userId, int $no, Carbon $now): void
    {
        if (!Schema::hasTable('terapis')) return;

        $exists = DB::table('terapis')->where('user_id', $userId)->first();
        if ($exists) return;

        $payload = ['user_id' => $userId];

        if (Schema::hasColumn('terapis', 'kode_terapis')) {
            $payload['kode_terapis'] = sprintf('TRP-%03d', $no);
        }
        if (Schema::hasColumn('terapis', 'jabatan')) {
            $payload['jabatan'] = 'Senior Beautician';
        }
        if (Schema::hasColumn('terapis', 'pengalaman_tahun')) {
            $payload['pengalaman_tahun'] = random_int(1, 8);
        }
        if (Schema::hasColumn('terapis', 'status_kerja')) {
            $payload['status_kerja'] = 'aktif';
        }
        if (Schema::hasColumn('terapis', 'created_at')) $payload['created_at'] = $now;
        if (Schema::hasColumn('terapis', 'updated_at')) $payload['updated_at'] = $now;

        DB::table('terapis')->insert($payload);
    }

    private function upsertJadwal(
        int $userId,
        string $jenisLayanan,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        int $durasiSlotMenit,
        Carbon $now
    ): void {
        $exists = DB::table('jadwal_praktiks')
            ->where('user_id', $userId)
            ->where('jenis_layanan', $jenisLayanan)
            ->where('hari', $hari)
            ->where('jam_mulai', $jamMulai)
            ->where('jam_selesai', $jamSelesai)
            ->first();

        if ($exists) return;

        DB::table('jadwal_praktiks')->insert([
            'user_id' => $userId,
            'jenis_layanan' => $jenisLayanan,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'durasi_slot_menit' => $durasiSlotMenit,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
