<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->string('nama')->nullable()->after('user_id');
            $table->date('tanggal_lahir')->nullable()->after('nama');
            $table->string('jenis_kelamin')->nullable()->after('tanggal_lahir');
            $table->string('no_telepon')->nullable()->after('jenis_kelamin');
            
            // Make user_id nullable for walk-in patients
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Copy demographic data from related users
        DB::statement("
            UPDATE pasien p
            JOIN users u ON p.user_id = u.id
            SET p.nama = u.name,
                p.tanggal_lahir = u.tanggal_lahir,
                p.jenis_kelamin = u.jenis_kelamin,
                p.no_telepon = u.phone
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropColumn(['nama', 'tanggal_lahir', 'jenis_kelamin', 'no_telepon']);
        });
    }
};
