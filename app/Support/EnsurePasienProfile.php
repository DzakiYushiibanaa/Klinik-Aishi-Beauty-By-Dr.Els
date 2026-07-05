<?php

namespace App\Support;

use App\Models\Pasien;
use App\Models\User;

/**
 * Helper terpusat untuk memastikan setiap user dengan role `pasien`
 * punya record yang valid di tabel `pasien`.
 *
 * Mengapa terpusat:
 *   - Ada beberapa entry point pembuatan akun pasien:
 *       * RegisteredUserController (registrasi form standar)
 *       * GoogleAuthController (registrasi via OAuth Google)
 *       * PosCreateOrderForm (admin buatkan pasien walk-in)
 *       * Filament Pasien resource (admin tambah manual)
 *   - Idempotent: pakai `firstOrCreate` supaya kalau dipanggil dua kali
 *     untuk user yang sama, profil tidak duplikat.
 *   - Konsisten kode: prefix `NM` untuk non-member, `RM` untuk member.
 *
 * Cara pakai:
 *   ```php
 *   $user = User::create([...]);
 *   $user->assignRole('pasien');
 *
 *   EnsurePasienProfile::for($user);
 *   // atau dengan data tambahan dari form:
 *   EnsurePasienProfile::for($user, [
 *       'no_telepon' => $request->phone,
 *       'is_member'  => false,
 *   ]);
 *   ```
 */
class EnsurePasienProfile
{
    /**
     * Pastikan user punya profil Pasien. Kalau belum ada, buatkan dengan
     * data default (no_rekam_medis NM/RM otomatis).
     *
     * @param  User  $user      User yang baru/sudah ada.
     * @param  array $overrides Data tambahan untuk record Pasien (mis. no_telepon).
     * @return Pasien
     */
    public static function for(User $user, array $overrides = []): Pasien
    {
        // Defensive: kalau user belum ada di DB, jangan dibuat record yang link
        // ke user_id null/0.
        if (! $user->exists || ! $user->id) {
            throw new \InvalidArgumentException(
                'EnsurePasienProfile::for() butuh User yang sudah tersimpan ke database.'
            );
        }

        // Kalau profil sudah ada → pakai itu, tidak perlu generate kode baru.
        $existing = Pasien::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        $isMember = (bool) ($overrides['is_member'] ?? false);
        $prefix   = $isMember ? 'RM' : 'NM';

        $payload = array_merge([
            'user_id'        => $user->id,
            'nama'           => $overrides['nama'] ?? $user->name,
            'no_telepon'     => $overrides['no_telepon'] ?? $user->phone,
            'tanggal_lahir'  => $overrides['tanggal_lahir'] ?? null,
            'jenis_kelamin'  => $overrides['jenis_kelamin'] ?? null,
            'no_rekam_medis' => $overrides['no_rekam_medis']
                ?? CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', $prefix, 6, '-'),
            'is_member'      => $isMember,
            'member_since'   => $isMember ? ($overrides['member_since'] ?? now()) : null,
        ], $overrides);

        // firstOrCreate untuk safety: kalau ada race condition, tetap aman.
        return Pasien::firstOrCreate(
            ['user_id' => $user->id],
            $payload
        );
    }
}
