<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom OAuth (Google) di tabel users.
 *
 *  - google_id   : ID unik dari Google (dipakai untuk identifikasi ulang).
 *  - avatar_url  : URL foto profil dari Google.
 *  - password    : dijadikan nullable supaya user yang register lewat
 *                  Google tidak perlu password lokal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url', 500)->nullable()->after('google_id');
            }
        });

        // Make password nullable (separate statement, doctrine/dbal not needed for change()
        // — we use raw SQL fallback for MySQL/MariaDB and SQLite tolerance).
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // doctrine/dbal might not be installed — fall back to raw SQL for MySQL/MariaDB.
            $driver = config('database.default');
            if (in_array(config("database.connections.$driver.driver"), ['mysql', 'mariadb'])) {
                \DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar_url')) {
                $table->dropColumn('avatar_url');
            }
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            }
        });
    }
};
