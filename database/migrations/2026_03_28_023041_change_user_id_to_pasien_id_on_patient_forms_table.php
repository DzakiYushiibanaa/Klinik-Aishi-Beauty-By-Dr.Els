<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('patient_forms', 'pasien_id')) {
            Schema::table('patient_forms', function (Blueprint $table) {
                $table->unsignedBigInteger('pasien_id')->nullable()->after('user_id');
            });
        }

        // Always run the data mapping just in case it crashed midway last time.
        if (DB::connection()->getDriverName() === 'sqlite') {
            $userIds = DB::table('patient_forms')
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id');

            foreach ($userIds as $userId) {
                $exists = DB::table('pasien')
                    ->where('user_id', $userId)
                    ->exists();

                if (!$exists) {
                    $user = DB::table('users')->where('id', $userId)->first();
                    if ($user) {
                        DB::table('pasien')->insert([
                            'user_id' => $user->id,
                            'nama' => $user->name,
                            'tanggal_lahir' => $user->tanggal_lahir,
                            'jenis_kelamin' => $user->jenis_kelamin,
                            'no_telepon' => $user->phone,
                            'no_rekam_medis' => 'NM-OL' . $user->id,
                            'is_member' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::table('patient_forms')
                ->whereNotNull('user_id')
                ->orderBy('patient_form_id')
                ->each(function ($form) {
                    $pasienId = DB::table('pasien')
                        ->where('user_id', $form->user_id)
                        ->value('pasien_id');

                    if (!$pasienId) return;

                    DB::table('patient_forms')
                        ->where('patient_form_id', $form->patient_form_id)
                        ->update(['pasien_id' => $pasienId]);
                });
        } else {
            DB::statement("
                INSERT IGNORE INTO pasien (user_id, nama, tanggal_lahir, jenis_kelamin, no_telepon, no_rekam_medis, is_member, created_at, updated_at)
                SELECT DISTINCT u.id, u.name, u.tanggal_lahir, u.jenis_kelamin, u.phone, CONCAT('NM-OL', u.id), 0, NOW(), NOW()
                FROM users u
                JOIN patient_forms pf ON u.id = pf.user_id
                WHERE pf.user_id NOT IN (SELECT p2.user_id FROM pasien p2 WHERE p2.user_id IS NOT NULL)
            ");

            DB::statement("
                UPDATE patient_forms pf
                JOIN pasien p ON pf.user_id = p.user_id
                SET pf.pasien_id = p.pasien_id
            ");
        }

        // Safely drop keys and the column
        Schema::table('patient_forms', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex(['user_id', 'status']);
            } catch (\Exception $e) {}
        });

        if (Schema::hasColumn('patient_forms', 'user_id')) {
            Schema::table('patient_forms', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    public function down(): void
    {
        // ... (down is ok since development env usually migrates up)
    }
};
