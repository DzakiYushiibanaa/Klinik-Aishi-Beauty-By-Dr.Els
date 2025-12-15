<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles (Spatie)
        $roles = ['super_admin', 'admin', 'dokter', 'terapis', 'apoteker', 'pasien'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // Helper: isi hanya kolom yang benar-benar ada di tabel users
        $fillIfExists = function (array $data): array {
            $out = [];
            foreach ($data as $k => $v) {
                if (Schema::hasColumn('users', $k)) {
                    $out[$k] = $v;
                }
            }
            return $out;
        };

        $createUser = function (string $name, string $email, string $role, string $password) use ($fillIfExists) {
            $base = [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),

                // kalau kamu masih punya kolom "role" di users, ini akan otomatis keisi
                'role' => $role,

                // optional (akan keisi kalau kolomnya ada)
                'phone' => '081234567890',
                'jenis_kelamin' => 'laki-laki',
                'is_member' => $role === 'pasien' ? 1 : 0,
            ];

            $user = User::updateOrCreate(
                ['email' => $email],
                $fillIfExists($base)
            );

            // Assign role Spatie
            $user->syncRoles([$role]);

            return $user;
        };

        // Akun 5 aktor (+ super admin biar enak testing Shield)
        $createUser('Super Admin', 'superadmin@test.com', 'super_admin', 'password123');
        $createUser('Admin', 'admin@test.com', 'admin', 'password123');
        $createUser('Dokter', 'dokter@test.com', 'dokter', 'password123');
        $createUser('Terapis', 'terapis@test.com', 'terapis', 'password123');
        $createUser('Apoteker', 'apoteker@test.com', 'apoteker', 'password123');
        $createUser('Pasien', 'pasien@test.com', 'pasien', 'password123');

        // Kalau permission sudah ada (misal setelah shield:generate), kasih semua ke super_admin
        $super = Role::where('name', 'super_admin')->first();
        if ($super && Permission::count() > 0) {
            $super->syncPermissions(Permission::all());
        }
    }
}
