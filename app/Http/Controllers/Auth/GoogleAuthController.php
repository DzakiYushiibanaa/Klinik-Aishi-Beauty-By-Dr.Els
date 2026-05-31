<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EnsurePasienProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

/**
 * Login & registrasi via akun Google (OAuth).
 *
 * Alur:
 *   1. User klik tombol "Masuk dengan Google" / "Daftar dengan Google"
 *      → redirect ke Google.
 *   2. Google callback ke /auth/google/callback:
 *      - Kalau user dengan google_id sama sudah ada → login.
 *      - Kalau user dengan email yang sama sudah ada (registrasi pakai
 *        password manual) → link akun (isi google_id, simpan), lalu login.
 *      - Kalau belum ada → buat user baru + Pasien profile (sama seperti
 *        flow registrasi manual), assign role 'pasien', login.
 */
class GoogleAuthController extends Controller
{
    /**
     * Redirect ke Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Callback dari Google. Hanya ada satu callback untuk "masuk" maupun
     * "daftar" — keduanya idempotent.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')
                ->with('error', 'Gagal masuk dengan Google. Silakan coba lagi.');
        }

        $googleId = $googleUser->getId();
        $email    = $googleUser->getEmail();
        $name     = $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Pengguna');
        $avatar   = $googleUser->getAvatar();

        if (!$email) {
            return redirect()->route('login')
                ->with('error', 'Akun Google tidak memiliki email yang dapat diakses.');
        }

        // 1. Cari user existing berdasarkan google_id atau email.
        $user = User::where('google_id', $googleId)->first();

        if (!$user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Akun email sudah ada (mungkin registrasi manual). Link ke Google.
                $user->update([
                    'google_id'         => $googleId,
                    'avatar_url'        => $user->avatar_url ?: $avatar,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            }
        }

        // 2. Belum ada — buat baru + Pasien profile (mirror flow registrasi manual).
        if (!$user) {
            DB::transaction(function () use (&$user, $googleId, $email, $name, $avatar) {
                $user = User::create([
                    'name'              => $name,
                    'email'             => $email,
                    'google_id'         => $googleId,
                    'avatar_url'        => $avatar,
                    'password'          => null, // login pakai Google, tanpa password lokal
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('pasien');

                // Setiap user dengan role pasien wajib punya record di tabel `pasien`.
                // Pakai service terpusat agar konsisten dengan flow registrasi manual.
                EnsurePasienProfile::for($user);

                event(new Registered($user));
            });
        }

        Auth::login($user, true);

        // Gate tambahan: staf yang sudah dinonaktifkan tidak boleh login —
        // walau lewat Google. Logout lagi & kembalikan ke /login dengan
        // pesan yang jelas.
        if (! \App\Support\AuthRedirect::isStaffActive($user)) {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda sedang dinonaktifkan. Hubungi admin klinik untuk informasi lebih lanjut.');
        }

        // Staf klinik → /admin, pasien & user biasa → /dashboard / landing.
        $target = \App\Support\AuthRedirect::isStaff($user)
            ? '/admin'
            : route('landing', absolute: false);

        return redirect()->intended($target)
            ->with('success', 'Berhasil masuk dengan Google.');
    }
}
