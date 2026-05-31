<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\EnsurePasienProfile;
use Illuminate\Console\Command;

/**
 * Backfill profil Pasien untuk user lama yang punya role `pasien`
 * tetapi belum punya record di tabel `pasien`.
 *
 * Pemicu:
 *   - Sebelum service `EnsurePasienProfile` ada, beberapa user mungkin
 *     terdaftar tanpa pasti dibuat record `pasien` (mis. registrasi
 *     OAuth pre-fix, atau seeder lama).
 *
 * Cara pakai:
 *   php artisan pasien:backfill            (jalankan)
 *   php artisan pasien:backfill --dry-run  (preview jumlah saja)
 */
class BackfillPasienProfiles extends Command
{
    protected $signature = 'pasien:backfill {--dry-run : Hanya tampilkan jumlah, tidak menulis ke DB}';
    protected $description = 'Buat record Pasien untuk semua user role pasien yang belum punya profil';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = User::role('pasien')
            ->whereDoesntHave('pasienProfile')
            ->orderBy('id');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Semua user pasien sudah punya profil. Tidak ada yang perlu dibackfill.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$total} user pasien tanpa profil.");

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada perubahan ditulis.');
            return self::SUCCESS;
        }

        $progress = $this->output->createProgressBar($total);
        $progress->start();

        $created = 0;
        $skipped = 0;
        $failed = 0;

        $query->chunkById(100, function ($users) use (&$created, &$skipped, &$failed, $progress) {
            foreach ($users as $user) {
                try {
                    $before = $user->pasienProfile()->exists();
                    EnsurePasienProfile::for($user);
                    $after = $user->pasienProfile()->exists();

                    if (! $before && $after) {
                        $created++;
                    } else {
                        $skipped++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $this->newLine();
                    $this->error("Gagal backfill user #{$user->id} ({$user->email}): {$e->getMessage()}");
                }

                $progress->advance();
            }
        });

        $progress->finish();
        $this->newLine(2);

        $this->info("Selesai. Dibuat: {$created} • Dilewati: {$skipped} • Gagal: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
