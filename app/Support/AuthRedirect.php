<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Tentukan tujuan redirect setelah login berhasil.
 *
 * Aturan:
 *   - Staf klinik (super_admin / admin / dokter / terapis / apoteker / kasir)
 *     diarahkan ke panel Filament `/admin`.
 *   - Pasien dan user tanpa role staf diarahkan ke `/dashboard` pasien.
 *
 * Helper ini dipakai bersama di semua entry point login:
 *   - AuthenticatedSessionController (form login standar)
 *   - GoogleAuthController (login via Google OAuth)
 *
 * Memastikan route admin betul-betul terpisah dari user biasa.
 */
class AuthRedirect
{
    public const STAFF_ROLES = [
        'super_admin',
        'admin',
        'dokter',
        'terapis',
        'apoteker',
        'kasir',
    ];

    /**
     * Resolve URL tujuan setelah login berhasil.
     *
     * @param  Authenticatable|null  $user
     * @return string
     */
    public static function homeFor(?Authenticatable $user): string
    {
        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(self::STAFF_ROLES)) {
            return '/admin';
        }

        return route('dashboard', absolute: false);
    }

    /**
     * Cek apakah user adalah staf klinik (boleh akses panel admin).
     */
    public static function isStaff(?Authenticatable $user): bool
    {
        return $user
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(self::STAFF_ROLES);
    }

    /**
     * Cek apakah akun staf sedang aktif berdasarkan status praktik/kerja.
     *
     * Aturan:
     *   - Dokter: aktif kalau `dokter.status_praktik = aktif`.
     *     Kalau profil dokter belum ada / kosong, dianggap nonaktif
     *     (admin belum melengkapi data praktik).
     *   - Terapis: aktif kalau `terapis.status_kerja = aktif`.
     *     Kalau profil terapis belum ada, dianggap nonaktif.
     *   - Role staf lain (admin, super_admin, kasir, apoteker) tidak
     *     punya profil status — selalu dianggap aktif.
     *   - Non-staf (pasien / tanpa role) — return true (cek aktif tidak
     *     berlaku untuk mereka, login pasien tidak perlu gate ini).
     *
     * Dipakai di entry point login (LoginRequest, GoogleAuthController)
     * untuk memblokir staf yang sudah dinonaktifkan.
     */
    public static function isStaffActive(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        // Bukan staf → tidak ada gate status, lolos.
        if (! self::isStaff($user)) {
            return true;
        }

        // Staf dokter → wajib status_praktik = aktif.
        if (method_exists($user, 'hasRole') && $user->hasRole('dokter')) {
            $profile = method_exists($user, 'dokterProfile')
                ? $user->dokterProfile
                : null;
            $status = $profile?->status_praktik;
            return is_string($status) && strtolower($status) === 'aktif';
        }

        // Staf terapis → wajib status_kerja = aktif.
        if (method_exists($user, 'hasRole') && $user->hasRole('terapis')) {
            $profile = method_exists($user, 'terapisProfile')
                ? $user->terapisProfile
                : null;
            $status = $profile?->status_kerja;
            return is_string($status) && strtolower($status) === 'aktif';
        }

        // Role staf lainnya tidak punya kolom status → selalu aktif.
        return true;
    }
}
