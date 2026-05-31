<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah aturan handler treatment di level kategori.
 *
 *   - doctor_allowed     : kategori boleh ditangani oleh dokter
 *   - therapist_allowed  : kategori boleh ditangani oleh terapis
 *
 * Default keduanya `true` untuk backward compatibility supaya kategori
 * yang sudah ada tidak tiba-tiba memblokir flow existing.
 *
 * Setelah migration jalan, blok `up()` juga melakukan seed data:
 * kategori yang TipenYa medis/injeksi (Laser, PRP, HIFU, Skin Booster,
 * Injection) di-set ke `therapist_allowed = false` agar dropdown UI
 * hanya menampilkan dokter.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('treatment_categories', function (Blueprint $table) {
            $table->boolean('doctor_allowed')->default(true)->after('is_active');
            $table->boolean('therapist_allowed')->default(true)->after('doctor_allowed');
        });

        // Seed: kategori yang HANYA boleh dokter.
        // Match by lowercase substring agar fleksibel (mis. "Pico Laser Treatment").
        $doctorOnlyKeywords = [
            'laser', 'booster', 'botox', 'injection', 'inject',
            'prp', 'hifu', 'fractional',
        ];

        $rows = DB::table('treatment_categories')->select('treatment_category_id', 'nama')->get();
        foreach ($rows as $row) {
            $name = strtolower((string) $row->nama);

            $isDoctorOnly = false;
            foreach ($doctorOnlyKeywords as $kw) {
                if (str_contains($name, $kw)) {
                    $isDoctorOnly = true;
                    break;
                }
            }

            if ($isDoctorOnly) {
                DB::table('treatment_categories')
                    ->where('treatment_category_id', $row->treatment_category_id)
                    ->update([
                        'doctor_allowed'    => true,
                        'therapist_allowed' => false,
                    ]);
            }
            // Lainnya tetap default (doctor_allowed=true, therapist_allowed=true).
        }
    }

    public function down(): void
    {
        Schema::table('treatment_categories', function (Blueprint $table) {
            $table->dropColumn(['doctor_allowed', 'therapist_allowed']);
        });
    }
};
